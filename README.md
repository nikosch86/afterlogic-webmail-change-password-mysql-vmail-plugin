# aurora-module-mail-change-password-mysql-vmail-plugin

Allows users to change the passwords of their email accounts on MySQL based vMail setups.

How to install a module (taking WebMail Lite as an example of the product built on Aurora framework): [Adding modules in WebMail Lite](https://afterlogic.com/docs/webmail-lite-8/installation/adding-modules)

In `data/settings/modules/MailChangePasswordMysqlVmailPlugin.config.json` file, you need to supply array of mailserver hostnames or IP addresses the feature is enabled for. If you put "*" item there, it means the feature is enabled for all accounts.

In the same file, you need to provide MySQL credentials to access the database.
