# Download 
[![](https://poggit.pmmp.io/shield.state/PHqx-Client)](https://poggit.pmmp.io/p/PHqx-Client)
<a href="https://poggit.pmmp.io/p/PHqx-Client"><img src="https://poggit.pmmp.io/shield.state/PHqx-Client"></a>
# About
- **PHqx or Phqzing Hacks** is a Server Sided Hack "Client" you can use for trolling friends (if you have one) or destroying other people at pvp.
- I am planning on updating this often and adding more features so if you have any suggestions feel free to dm me on Discord or open up an [Issue](https://github.com/Phqzing/PHax/issues)

# Features
- Kill Aura
- Reach
- Speed
- Anti Knockback
- GUI (for editing settings)

# Commands and Config
### Commands
```
.help
.inject
.eject
.toggle killaura
.toggle reach
.toggle speed
.toggle antikb
.killaura edit
.reach edit
.speed edit
.antikb edit
```
For more info just type ".help" in chat and it will show you the commands and what it's used for

### Config
```yml
---
# the plugin won't work on worlds that are listed here
# you can add as many worlds as you wan't but make sure you are getting the folder name of the world
black-listed-worlds:
  - "exampleWorld1"
  - "exampleWorld2"
  - "exampleWorld3"


# this is how often the Kill Aura checks for nearby players
# Note: value must always be positive
# 20 = 1 second
killaura-tickrate: 10


# DO NOT TOUCH UNLESS YOU KNOW EXACTLY WHAT YOU'RE DOING
database:
  type: sqlite
  sqlite:
    file: settings.sql
  worker-limit: 1
...
```

# Discord
### Phqzing#9470
Feel free to message me if you have any questions or suggestions regarding this plugin
