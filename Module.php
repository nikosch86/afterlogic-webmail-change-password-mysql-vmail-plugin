<?php
/**
 * This code is licensed under AGPLv3 license or AfterLogic Software License
 * if commercial version of the product was purchased.
 * For full statements of the licenses see LICENSE-AFTERLOGIC and LICENSE-AGPL3 files.
 */

namespace Aurora\Modules\MailChangePasswordMysqlVmailPlugin;

/**
 * This module gives users the ability to change their passwords.
 *
 * @license https://www.gnu.org/licenses/agpl-3.0.html AGPL-3.0
 * @license https://afterlogic.com/products/common-licensing AfterLogic Software License
 * @copyright Copyright (c) 2018, Afterlogic Corp.
 *
 * @package Modules
 */
class Module extends \Aurora\System\Module\AbstractModule
{
	/**
	 * @param CApiPluginManager $oPluginManager
	 */

	public function init()
	{
		$this->oMailModule = \Aurora\System\Api::GetModule('Mail');

		$this->subscribeEvent('Mail::ChangePassword::before', array($this, 'onBeforeChangePassword'));
	}

	/**
	 *
	 * @param array $aArguments
	 * @param mixed $mResult
	 */
	public function onBeforeChangePassword($aArguments, &$mResult)
	{
		$mResult = true;

		$oAccount = $this->oMailModule->GetAccount($aArguments['AccountId']);

		if ($oAccount && $this->checkCanChangePassword($oAccount))
		{
			$mResult = $this->сhangePassword($oAccount, $aArguments['NewPassword']);
		}
	}

	/**
	 * @param CAccount $oAccount
	 * @return bool
	 */
	protected function checkCanChangePassword($oAccount)
	{
		$bFound = in_array("*", $this->getConfig('SupportedServers', array()));

		if (!$bFound)
		{
			$oServer = $this->oMailModule->GetServer($oAccount->ServerId);
			if ($oServer && in_array($oServer->Name, $this->getConfig('SupportedServers')))
			{
				$bFound = true;
			}
		}

		return $bFound;
	}

	/**
	 * @param CAccount $oAccount
	 */
	protected function сhangePassword($oAccount, $sPassword)
	{
	    $bResult = false;
	    if (12 < strlen($oAccount->IncomingPassword) && $oAccount->IncomingPassword !== $sPassword )
			{
				$vmail_host 	= $this->getConfig('DbHost', '');
				$vmail_name 	= $this->getConfig('DbName', '');
				$vmail_dbuser = $this->getConfig('DbUser','');
				$vmail_dbpass = $this->getConfig('DbPass','');

				$mysqli = mysqli_connect($vmail_host, $vmail_dbuser, $vmail_dbpass, $vmail_name);
				if ($mysqli)
				{
					$newPassword = mysqli_real_escape_string($mysqli, $oAccount->IncomingLogin);
					$sql = "UPDATE users SET password='".$sPassword."' WHERE email='".$newPassword."'";
					$bResult = mysqli_query($mysqli, $sql);
					if (!$bResult) {
						throw new \Aurora\System\Exceptions\ApiException(\Aurora\System\Exceptions\Errs::UserManager_AccountNewPasswordUpdateError);
					}
					mysqli_close($mysqli);
				} else {
					throw new \Aurora\System\Exceptions\ApiException(\Aurora\System\Exceptions\Errs::UserManager_AccountNewPasswordUpdateError);
				}
	    }
	    return $bResult;
	}

	public function GetSettings()
	{
		\Aurora\System\Api::checkUserRoleIsAtLeast(\Aurora\System\Enums\UserRole::Anonymous);

		$sSupportedServers = implode("\n", $this->getConfig('SupportedServers', array()));

		$aAppData = array(
			'SupportedServers' => $sSupportedServers,
			'DbUser' => $this->getConfig('DbUser', ''),
			'DbPass' => $this->getConfig('DbPass', ''),
			'DbHost' => $this->getConfig('DbHost', ''),
			'DbName' => $this->getConfig('DbName', ''),
		);

		return $aAppData;
	}

	public function UpdateSettings($SupportedServers, $DbUser, $DbPass, $DbHost, $DbName)
	{
		\Aurora\System\Api::checkUserRoleIsAtLeast(\Aurora\System\Enums\UserRole::TenantAdmin);

		$aSupportedServers = preg_split('/\r\n|[\r\n]/', $SupportedServers);

		$this->setConfig('SupportedServers', $aSupportedServers);
		$this->setConfig('DbUser', $DbUser);
		$this->setConfig('DbPass', $DbPass);
		$this->setConfig('DbHost', $DbHost);
		$this->setConfig('DbName', $DbName);
		$this->saveModuleConfig();
		return true;
	}
}
