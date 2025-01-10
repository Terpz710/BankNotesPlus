# BankNotesPlus
Allows the creation of bank notes using either Mineconomy, BedrockEcomony or EcomonyAPI. This plugin is mostly designed so other plugins can use this as a dependency to add bank notes to your specifed plugin of choice.
Of course this plugin works as a normal bank notes plugin so do as you will. 

# Features
- Configurable messages
- Customizable BankNote item
- Not broken unlike other banknote plugins ;)

**Plugins that support BankNotesPlus**

- EnderKits

# API
**Get the banknote instance**
```
$bankNotesPlus = BankNotesPlus::getInstance();
```
**How to get the bank note**
```
$player is an instance of Player::class

$amount = 100;

$bankNoteItem = $bankNotesPlus->convertToBankNote($player, $amount);
```
# Report a bug

Need to report a bug?

Please provide any error logs from the crash dump or console or type a clear way to reproduce it.

[Click me!](https://github.com/Terpz710/AntiAltAccounts/issues/new)

# Want to contribute?

Note: I will not be merging any request! If the change is too small then it wont be accepted, dm me the issue instead.

[click me!](https://github.com/Terpz710/AntiAltAccounts/pulls)
