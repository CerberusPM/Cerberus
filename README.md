# Cerberus
**Cerberus** is an essential tool for protecting server structures. It allows server administrators and players to easily protect their builds.
If you are familiar with WorldGuard for Java Edition servers, this plugin aims to acheive similar functionality.
Built By Server Owners for Server Owners!

## Features
* Customizable selection tool (axe by default) and selection setting, expansion commands
* Landclaim creation&deletion
* Spawnpoint setting and teleportation
* Ability to add owners/members to landclaims
* All plugin messages are customizable and support color codes
* Easy translation using language files (for now there are only English and Russian, more will be added soon)
* Powerful flag system (check out "Flag System section of this document)

## Commands & Permissions

| **Command**                                                     | **Permission**                                                                                                                                                              | **Description**                                                                                                                                                                     |
|-----------------------------------------------------------------|-----------------------------------------------------------------------------------------------------------------------------------------------------------------------------|-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| `/cerberus`                                                     | `cerberus.command`                                                                                                                                                          | Base permission for the main command                                                                                                                                                |
| `/cerberus claim <name>`                                        | `cerberus.command.claim`<br/>`cerberus.command.claim.bypass_intersect`<br/>`cerberus.command.claim.count_limit.unlimited`<br/>`cerberus.command.claim.area_limit.unlimited` | Allows claiming land.  <br/>Allows intersecting land claims created by other players.<br/>Bypasses land claim count limit if enabled.<br/>Bypasses land claim area limit if enabled |
| `/cerberus flag`                                                | `cerberus.command.flag`                                                                                                                                                     | Base permission for setting flags                                                                                                                                                   |
| `/cerberus help`                                                | `cerberus.command.help`                                                                                                                                                     | Allows access to help documentation                                                                                                                                                 |
| `/cerberus here`                                                | `cerberus.command.here`                                                                                                                                                     | Displays land claim information at the player's location                                                                                                                            |
| `/cerberus info`                                                | `cerberus.command.info`                                                                                                                                                     | Provides information about a specific land claim                                                                                                                                    |
| `/cerberus list`                                                | `cerberus.command.list`                                                                                                                                                     | Lists owned land claims                                                                                                                                                             |
| `/cerberus list <player>`                                       | `cerberus.command.list.other`                                                                                                                                               | Allows listing land claims of other players                                                                                                                                         |
| `/cerberus move`                                                | `cerberus.command.move`                                                                                                                                                     | Moves an existing land claim                                                                                                                                                        |
| `/cerberus remove`                                              | `cerberus.command.remove`                                                                                                                                                   | Removes an owned land claim                                                                                                                                                         |
| `/cerberus remove <name>`                                       | `cerberus.command.remove.other`                                                                                                                                             | Allows removing land claims owned by other players                                                                                                                                  |
| `/cerberus pos1`, `/pos2`, `/cerberus move`, `/cerberus expand` | `cerberus.command.selection`                                                                                                                                                | Allows position selection, wand selection, movement, and expansion                                                                                                                  |
| `/cerberus setspawn`                                            | `cerberus.command.setspawn`                                                                                                                                                 | Sets a spawn point for a land claim                                                                                                                                                 |
| `/cerberus setspawn <name>`                                     | `cerberus.command.setspawn.other`                                                                                                                                           | Allows setting spawn points for land claims owned by others                                                                                                                         |
| `/cerberus teleport`                                            | `cerberus.command.teleport`                                                                                                                                                 | Teleports to a claim's spawn point                                                                                                                                                  |
| `/cerberus teleport <player>`                                   | `cerberus.command.teleport.other`                                                                                                                                           | Allows teleporting other players to spawn points                                                                                                                                    |
| `/cerberus teleport to <player>`                                | `cerberus.command.teleport.to.other`                                                                                                                                        | Allows teleporting to another player's land claim spawn point                                                                                                                       |
| `/cerberus addmember`                                           | `cerberus.command.addmember`<br/>`cerberus.command.addmember.other`                                                                                                         | Permission for /cerberus addmember<br/>Permission to add members to landclaims owned by other players                                                                               |
| `/cerberus addowner`                                            | `cerberus.command.addowner`<br/>`cerberus.command.addowner.other`                                                                                                           | Permission for /cerberus addowner<br/>Permission to add owners to landclaims owned by other players                                                                                 |
| `/cerberus removemember`                                        | `cerberus.command.removemember`<br/>`cerberus.command.removemember.other`                                                                                                   | Permission for /cerberus removemember<br/>  Permission to remove members from landclaims owned by other players                                                                     |
| `/cerberus removeowner`                                         | `cerberus.command.removeowner`<br/>`cerberus.command.removeowner.other`                                                                                                     | Permission for /cerberus removeowner<br/>Permission to remove owners from landclaims owned by other players                                                                         |
| `/cerberus unsetspawn`                                          | `cerberus.command.unsetspawn`                                                                                                                                               | Removes a spawn point from a land claim                                                                                                                                             |
| `/cerberus unsetspawn <name>`                                   | `cerberus.command.unsetspawn.other`                                                                                                                                         | Allows removing spawn points from land claims owned by others                                                                                                                       |
| `/cerberus wand`                                                | `cerberus.command.wand`                                                                                                                                                     | Gives you a selection tool                                                                                                                                                          |
| `/cerberus reload`                                              | `cerberus.command.reload`                                                                                                                                                   | Reloads the plugin configuration                                                                                                                                                    |

## Flag System
Cerberus posesses a powerful flag system. Flags allow you to toggle certain protection and utilitary features in your landclaims.
Flags are set using: /cerberus flag <land_name> <flag_name/id/alias> <true/false> if user has got permission for chosen flag. All flags also have got bypass permissions for
privileged users.
### Currently implemented flags
| **Flag Name** | **Flag ID** | **Description**                                                  | **Permissions**                                               | **Aliases**                                                                                                        |
|---------------|-------------|------------------------------------------------------------------|---------------------------------------------------------------|--------------------------------------------------------------------------------------------------------------------|
| NoBreak       | no_break    | Prevent block breaking                                           | cerberus.flag.no_place <br>cerberus.flag.no_place.bypass      | break, breaking, nobreaking, no_breaking, stop_breaking, stopbreaking                                              |
| NoPlace       | no_place    | Prevent block placing                                            |cerberus.flag.no_break <br>cerberus.flag.no_break.bypass       | noplacing, no_placing, nobuild, no_build, place, build, placing, building                                          |
| NoInteract    | no_interact | Prevent interaction with blocks (opening, using, pressing, etc.) |cerberus.flag.no_interact <br>cerberus.flag.no_interact.bypass | nouse, no_use, interact, notouch, no_touch, no_using, nousing, no_interacting, nointeracting, use, touch, interact |
These ones are turned on by default for all newly created landclaims. This can be tuned in config.yml
In future more flags will be added. The system allows to add a myriad of interesting features. THese will include pvp and godmode, messages, trespassing prevention, etc.

## Default Permissions
By default, all permissions are set to `op`. This means only server operators have access to these commands unless explicitly assigned to players.

## Default Configuration
```yaml
# Cerberus Configuration 

# Customizable prefix. Supports color codes
prefix: "&l&2+&e-&6Cerberus&e-&2+&r "

# Currently available languages: eng, rus
# Specify language file name here without .yml ending. If the language file doesn't exist and is available, it will appear in the plugin folder.
# You can create your own language files if you wish. It's possible by copying any other existing language file and translating it to your language, then specifying the file name below.
# If you want to help us develop this plugin, consider sending translated language file to us by forking our repository and opening a pull request with your file on github: https://github.com/Levonzie/Cerberus
language: eng

# -==Wand==-
wand-item: stone_axe # Given wand will be of this item type. Wands given before item type change will remain functional if wand-strict-item option is set to false
# The wand item should not be food or a projectile. Items of these types can't properly set positions.
wand-enchantments: # The wand will be enchanted
  protection: 10

wand-use-default-name: true # Whether to use the default wand name from language files
wand-use-default-lore: true # Whether to use the default wand lore from language files

# To edit wand name and lore below, first set wand-use-default-name and/or wand-use-default-lore options to false
# These work only if "wand-use-default-name" and "wand-use-default-lore" settings are set to false
wand-name: "&l&gCerberus Wand" # Given wand will have this name
wand-lore: # Wand item description
- "&aThis is a Cerberus wand"
- "&eUse it to claim your land"

# default-timezone and date-format are used in landclaim creation date information (/cerberus info <land name>)
default-timezone: '' # If empty or not set will detect server time zone automatically. Better set it, as automatic recognition is inaccurate. List of available timezones: https://www.php.net/manual/en/timezones.php
date-format: "Y-m-d H:i:s" # Formatting lookup table: https://www.php.net/manual/en/datetime.format.php#refsect1-datetime.format-parameters

# -==Claim limits==-
# Here you can enable/disable landclaim count and area limits set by permissions:
# - cerberus.command.claim.limit.count.<number> - set claim limit to number
# - cerberus.command.claim.limit.area.<number> - maximum allowed landclaim area (in square meters)
# Put 'unlimited' instead of number in order to disable a limit
landclaim-count-limit: false
default-landclaim-count-limit: 5 # This limit will be set for ones, not having any land count limit permission. Works only if landclaim-count-limit is enabled! "unlimited" works here as well
notify-user-when-count-limit-reached: true # Notification will be sent to user when they reach the limit (i.e., claim the last land within the limit)

landclaim-area-limit: false
default-landclaim-area-limit: 10000 # In square meters

# -==Command options==-
notify-player-on-teleportation: true #Whether player who gets teleported to a landclaim should be notified that they have been teleported

# -==Flags==--
# Default flags. These ones are applied to each newly created landclaim
default-flags:
  - no_break
  - no_place
  - no_interact
  
# Toggle flags globally. You can switch off certain flags you don't need to disable
#their events and save some server resources
enabled-flags:
  no_break: true
  no_place: true
  no_interact: true

version: 1.0-DEV-62 # For internal use. Don't touch!
```

## The Basics:
### How to claim land:
1. Do /cerberus wand or /crb wand to get the selection wand
2. Left click a block (break) to select the first position. Alternatively you can do /crb pos1 to set first position to where you're standing at, or even
specify the exact coordinates if you wish!
3. Use /crb expand <up/down/both> [number_of_blocks(up to the world height limit if not specified)] to expand the selection vertically
4. Do /crb claim <name> to create a landclaim

### Video Tutorial:
`Coming in Release 1.0.0`


# **Work in progress:**

## DEPENDENCIES:
CerberusPM uses [Commando](https://github.com/ACM-PocketMine-MP/Commando/tree/PM5/) (the Link is a Forked Version of Commando which remains UP TO DATE with CerberusPM. 

## Features yet to Implement
* Make help subcommand useful
* Move subcommand
* Add more flags
* Importing landclaims from other plugins, i.e. migration
* Region highlighting
* Update checking
* Region snapshotting and schematic saving

# Authors
This Plugin was Jointly Founded by Levonzie and skyss0fly in 2023.
