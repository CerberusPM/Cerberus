# Cerberus
**Cerberus** is an essential tool for protecting server structures. It allows server administrators and players to easily protect their builds.
If you are familiar with WorldGuard for Java Edition servers, this plugin aims to acheive similar functionality.
Built By Server Owners for Server Owners!

## Commands & Permissions

| **Command**                                                     | **Permission**                                                                                                                                                              | **Description**                                                                                                                                                                     |
|-----------------------------------------------------------------|-----------------------------------------------------------------------------------------------------------------------------------------------------------------------------|-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| `/cerberus`                                                     | `cerberus.command`                                                                                                                                                          | Base permission for the main command                                                                                                                                                |
| `/cerberus claim`                                               | `cerberus.command.claim`<br/>`cerberus.command.claim.bypass_intersect`<br/>`cerberus.command.claim.count_limit.unlimited`<br/>`cerberus.command.claim.area_limit.unlimited` | Allows claiming land.  <br/>Allows intersecting land claims created by other players.<br/>Bypasses land claim count limit if enabled.<br/>Bypasses land claim area limit if enabled |
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

## Default Permissions
By default, all permissions are set to `op`. This means only server operators have access to these commands unless explicitly assigned to players.


## The Basics:
**This Plugin Aims to Implement features that allow server administrators and players to protect their builds from griefers and even allow players to build with set flags and permissions. 

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
* Move subcommand
* Flag system and flag subcommand with many flags (like in WorldGuard for Java)
* Importing landclaims from other plugins, i.e. migration
* Region highlighting

# Authors
This Plugin was Jointly Founded by Levonzie and skyss0fly in 2023.
