# Revision History for the OLCS Backend ### 4.0 2016-09-23 - Version 4.0 is the first version of the OLCS Self-serve to be published to GitHub

## [6.0.0](https://github.com/dvsa/olcs-selfserve/compare/v5.2.0...v6.0.0) (2024-03-28)


### ⚠ BREAKING CHANGES

* interop/container no longer supported
* upgrade to Laminas v3 ([#17](https://github.com/dvsa/olcs-selfserve/issues/17))
* migrate to GitHub ([#6](https://github.com/dvsa/olcs-selfserve/issues/6))

### Features

* Allow uploads on a new conversation ([#95](https://github.com/dvsa/olcs-selfserve/issues/95)) ([0164d68](https://github.com/dvsa/olcs-selfserve/commit/0164d68c0d838c432416c8011b68183e2ae514b9))
* Back to conversations links ([#75](https://github.com/dvsa/olcs-selfserve/issues/75)) ([9a81483](https://github.com/dvsa/olcs-selfserve/commit/9a81483c3c112117a80173e8bc7ae2d6cee5f184))
* bump olcs-transfer to 6.5 ([#84](https://github.com/dvsa/olcs-selfserve/issues/84)) ([0af801b](https://github.com/dvsa/olcs-selfserve/commit/0af801b6bb738a7c51a360db2e92a84efffa59b3))
* Conversation subject title on message list ([#74](https://github.com/dvsa/olcs-selfserve/issues/74)) ([859fbe3](https://github.com/dvsa/olcs-selfserve/commit/859fbe3c87630930d449ea88de5db36253167e81))
* Enable/Disable Messaging File Upload ([#72](https://github.com/dvsa/olcs-selfserve/issues/72)) ([520fe03](https://github.com/dvsa/olcs-selfserve/commit/520fe03898044774c6dcda6019d682a058d5de5d))
* Merge project/messaging ([#59](https://github.com/dvsa/olcs-selfserve/issues/59)) ([4781f02](https://github.com/dvsa/olcs-selfserve/commit/4781f02289976ffbd124eeb38069a90a4d0910ba))
* migrate config to application ([#69](https://github.com/dvsa/olcs-selfserve/issues/69)) ([0e279c1](https://github.com/dvsa/olcs-selfserve/commit/0e279c13c88bae076e526f8f725aac77a42231a4))
* migrate to GitHub ([#6](https://github.com/dvsa/olcs-selfserve/issues/6)) ([75aac02](https://github.com/dvsa/olcs-selfserve/commit/75aac0220cdcc8232c1066b59d0e3123fca75bc6))
* Move file uploader above the send button ([#98](https://github.com/dvsa/olcs-selfserve/issues/98)) ([1ee634b](https://github.com/dvsa/olcs-selfserve/commit/1ee634b8feb1874845f8e870fa661805ef0ab601))
* remove OpenAM logic ([#101](https://github.com/dvsa/olcs-selfserve/issues/101)) ([899237e](https://github.com/dvsa/olcs-selfserve/commit/899237e7186b64e2db8c92ee90def43359e4b631))
* Unread counter on Messages tab ([#70](https://github.com/dvsa/olcs-selfserve/issues/70)) ([a325cb6](https://github.com/dvsa/olcs-selfserve/commit/a325cb6be4df7131e3cddb92996f080c208c7093))
* update cache directory of `HtmlPurifier` ([#104](https://github.com/dvsa/olcs-selfserve/issues/104)) ([6c6f968](https://github.com/dvsa/olcs-selfserve/commit/6c6f968b8a522f64fa77afb268de83746989faa4))
* update crown logo to Tudor Crown ([#55](https://github.com/dvsa/olcs-selfserve/issues/55)) ([45e5ca9](https://github.com/dvsa/olcs-selfserve/commit/45e5ca902318c48d48f0971419873cc7d62d4f0a))
* upgrade to Laminas v3 ([#17](https://github.com/dvsa/olcs-selfserve/issues/17)) ([3299b04](https://github.com/dvsa/olcs-selfserve/commit/3299b0421ac50dd0e8a0e73cb9c43309ea975540))
* Use custom messages for messaging when defined ([#99](https://github.com/dvsa/olcs-selfserve/issues/99)) ([acfbaa8](https://github.com/dvsa/olcs-selfserve/commit/acfbaa8cc8e594cb73d620fc443cf03ac49dfbe8))
* VOL-3691 switch to Psr Container ([#58](https://github.com/dvsa/olcs-selfserve/issues/58)) ([2cc08c1](https://github.com/dvsa/olcs-selfserve/commit/2cc08c152fe19241639bc827b9fa5d9f122c53b4))


### Bug Fixes

* add `chdir` to `govukaccount-redirect` to pickup correct cache directory ([#96](https://github.com/dvsa/olcs-selfserve/issues/96)) ([f1924a7](https://github.com/dvsa/olcs-selfserve/commit/f1924a71a7e290a8f56b6a15484815afaa4c0c8e))
* consolidate `Navigation` and `navigation` ([#43](https://github.com/dvsa/olcs-selfserve/issues/43)) ([72e5266](https://github.com/dvsa/olcs-selfserve/commit/72e526631b8dbaebdc9a896065ca5366ba085818))
* Disable reply on closed conversations ([#61](https://github.com/dvsa/olcs-selfserve/issues/61)) ([49b2cdc](https://github.com/dvsa/olcs-selfserve/commit/49b2cdc57b7535a5ec02a7f7071a9304f81dc101))
* fix error on EBSR pages ([#47](https://github.com/dvsa/olcs-selfserve/issues/47)) ([bfa48d3](https://github.com/dvsa/olcs-selfserve/commit/bfa48d3cfe788f1bc2f5ad71740c063c86646b68))
* fix manage user journey ([#90](https://github.com/dvsa/olcs-selfserve/issues/90)) ([9776957](https://github.com/dvsa/olcs-selfserve/commit/97769576b8a22fe65bb832f13e1e78c29d8e32da))
* fix secret name template ([#100](https://github.com/dvsa/olcs-selfserve/issues/100)) ([624a082](https://github.com/dvsa/olcs-selfserve/commit/624a082b684016eb3597185df096dfae0dadfb13))
* fix stolen community licence journey ([#27](https://github.com/dvsa/olcs-selfserve/issues/27)) ([3e2a048](https://github.com/dvsa/olcs-selfserve/commit/3e2a048c927fc1376a663233fe5f839bf92ca3ca))
* fix url helper calls in 2 templates ([#28](https://github.com/dvsa/olcs-selfserve/issues/28)) ([ea99391](https://github.com/dvsa/olcs-selfserve/commit/ea9939178f2d64a75e532b93a0003cefde0dc5ca))
* Hide file upload if uploads disabled. ([#85](https://github.com/dvsa/olcs-selfserve/issues/85)) ([09f65da](https://github.com/dvsa/olcs-selfserve/commit/09f65da1283a0ff87e047df4f9e2dafcf0710608))
* improve TOPS report error handling ([#54](https://github.com/dvsa/olcs-selfserve/issues/54)) ([45bb578](https://github.com/dvsa/olcs-selfserve/commit/45bb5784929c06672e546ce9da3968f8d3e15176))
* move `ebsr` view files to new location ([#34](https://github.com/dvsa/olcs-selfserve/issues/34)) ([5e5247f](https://github.com/dvsa/olcs-selfserve/commit/5e5247f8de47c5ba8a7e5385f63f9adafafe9c4b))
* Old string formatter name to FQCN and Import. ([#46](https://github.com/dvsa/olcs-selfserve/issues/46)) ([6cd6c88](https://github.com/dvsa/olcs-selfserve/commit/6cd6c8871c3c39aba07eed73af3e5eb09c9199a5))
* Other template fixes where getHelperPluginManager was being used causing errors ([#25](https://github.com/dvsa/olcs-selfserve/issues/25)) ([37d6f1d](https://github.com/dvsa/olcs-selfserve/commit/37d6f1dd3653e135926341cee5df0ae8f30bcfd0))
* refactor constants to static variables ([#15](https://github.com/dvsa/olcs-selfserve/issues/15)) ([c9c54e3](https://github.com/dvsa/olcs-selfserve/commit/c9c54e3de08fdc20b2741bca21fa2261b5c759f3))
* remove form unit tests ([#11](https://github.com/dvsa/olcs-selfserve/issues/11)) ([a58e05c](https://github.com/dvsa/olcs-selfserve/commit/a58e05c809821f7edc5945f3003c5f348874b7e3))
* remove real servicemanager from unit tests ([#48](https://github.com/dvsa/olcs-selfserve/issues/48)) ([e7247ca](https://github.com/dvsa/olcs-selfserve/commit/e7247ca3979ee77c8e3df971d004b5073f88d2cf))
* replace `laminas-form` with patched fork ([#33](https://github.com/dvsa/olcs-selfserve/issues/33)) ([9f5f88a](https://github.com/dvsa/olcs-selfserve/commit/9f5f88a8912d4484f04d4d221eaeaec16eae9be7))
* resolve search issues ([#24](https://github.com/dvsa/olcs-selfserve/issues/24)) ([d8f527a](https://github.com/dvsa/olcs-selfserve/commit/d8f527aa24fd9b311679f6f55e7fc145a3a3408e))
* set `memory_limit` to 4G in PHPUnit configuration ([#9](https://github.com/dvsa/olcs-selfserve/issues/9)) ([9954443](https://github.com/dvsa/olcs-selfserve/commit/99544434c99bb3d88baad692e3340686cc434901))
* Two constructor params were recently added, only one was being used and the other not passed in from the factory. Removed the unused one. Page now tests OK locally. ([#31](https://github.com/dvsa/olcs-selfserve/issues/31)) ([3275482](https://github.com/dvsa/olcs-selfserve/commit/3275482d0e4f3eaccbd02592426c48dc6ef6bbb0))
* VOL-3471: TM Journey uses Verify when GOV.UK Acc toggle is off ([1d7beda](https://github.com/dvsa/olcs-selfserve/commit/1d7beda1b5d7280eeaf64d54ccc0253298d52cd2))
* VOL-5103 update selfserve type of licence controller as it shares the updated controller from common ([#80](https://github.com/dvsa/olcs-selfserve/issues/80)) ([09119e7](https://github.com/dvsa/olcs-selfserve/commit/09119e716bc5a5a2fcae42481394e4a03926f6a3))


### Miscellaneous Chores

* add `data/cache` to gitignore ([#93](https://github.com/dvsa/olcs-selfserve/issues/93)) ([b0e4b99](https://github.com/dvsa/olcs-selfserve/commit/b0e4b996c1a5ace8ea65dfffa7c2b5cb60277d49))
* add Dependabot config ([#10](https://github.com/dvsa/olcs-selfserve/issues/10)) ([ab4d991](https://github.com/dvsa/olcs-selfserve/commit/ab4d9919a25ddf6bb46f82f895179457cb721e33))
* apply Rector 7.4 ruleset ([#94](https://github.com/dvsa/olcs-selfserve/issues/94)) ([3e0b752](https://github.com/dvsa/olcs-selfserve/commit/3e0b752322fb01f3a1dc9bc3cfcfd5184a566492))
* bump `olcs-common` to `5.0.0-beta.10` ([#56](https://github.com/dvsa/olcs-selfserve/issues/56)) ([ad0028a](https://github.com/dvsa/olcs-selfserve/commit/ad0028a7b621b8dacfc4666199b6dc15797d222e))
* bump `olcs-common` to `5.0.0-beta.4` ([#35](https://github.com/dvsa/olcs-selfserve/issues/35)) ([94d8107](https://github.com/dvsa/olcs-selfserve/commit/94d8107c322d2f10e13614990e62bd601ba0e998))
* bump `olcs-common` to `5.0.0-beta.5` ([#39](https://github.com/dvsa/olcs-selfserve/issues/39)) ([b5b007b](https://github.com/dvsa/olcs-selfserve/commit/b5b007bc0d50bb0751af72fad1a1da462087257d))
* bump `olcs-common` to `5.0.0-beta.6` ([#42](https://github.com/dvsa/olcs-selfserve/issues/42)) ([dba65cd](https://github.com/dvsa/olcs-selfserve/commit/dba65cd9d26b7aa2a2346f4a927000fac120d519))
* bump `olcs-common` to `5.0.0-beta.7` ([#44](https://github.com/dvsa/olcs-selfserve/issues/44)) ([62fc64d](https://github.com/dvsa/olcs-selfserve/commit/62fc64de6225d8849bde16bffe3ee7c9e8f494f4))
* bump `olcs-common` to `5.0.0-beta.8` ([#51](https://github.com/dvsa/olcs-selfserve/issues/51)) ([485784e](https://github.com/dvsa/olcs-selfserve/commit/485784ed243c0b1fc9fdc8ae47d8026ad91637b5))
* bump `olcs-common` to `5.0.0-beta.9` ([#52](https://github.com/dvsa/olcs-selfserve/issues/52)) ([f6f33f7](https://github.com/dvsa/olcs-selfserve/commit/f6f33f794e8b2c6da240d120dafb6a39e7cc99cf))
* bump `olcs-common` to `v6.1.1` ([#77](https://github.com/dvsa/olcs-selfserve/issues/77)) ([3e687ef](https://github.com/dvsa/olcs-selfserve/commit/3e687eff2423b808be0907cb1f9756719961c9d5))
* bump `olcs-transfer` to `v6.2.1` ([#76](https://github.com/dvsa/olcs-selfserve/issues/76)) ([62b0828](https://github.com/dvsa/olcs-selfserve/commit/62b0828c5600273affe5ca1c5922b2947bdc5b88))
* bump `olcs/olcs-common` ([#29](https://github.com/dvsa/olcs-selfserve/issues/29)) ([b6a25b3](https://github.com/dvsa/olcs-selfserve/commit/b6a25b3a3cee9bb4aa459b7ea9a38a7ecd2e7118))
* bump `olcs/olcs-common` to `5.0.0-beta.2` ([#26](https://github.com/dvsa/olcs-selfserve/issues/26)) ([68f43d2](https://github.com/dvsa/olcs-selfserve/commit/68f43d2cde04a27c4a6ad2ec8dbf9f89b349a0eb))
* bump `olcs/olcs-transfer` to `5.0.0-beta.8` ([#30](https://github.com/dvsa/olcs-selfserve/issues/30)) ([6b8ec94](https://github.com/dvsa/olcs-selfserve/commit/6b8ec947f3502da3a5dca288583bf12b10cc66e6))
* bump olcs-common ([#105](https://github.com/dvsa/olcs-selfserve/issues/105)) ([d792d93](https://github.com/dvsa/olcs-selfserve/commit/d792d937162866d8a90d8522adcf0c40601209f2))
* Change upload location for message files ([#83](https://github.com/dvsa/olcs-selfserve/issues/83)) ([3e433f3](https://github.com/dvsa/olcs-selfserve/commit/3e433f3946cd6ea6f259b99e255092749880aef0))
* tidy up laminas 2.5 compatability code ([#86](https://github.com/dvsa/olcs-selfserve/issues/86)) ([b07363c](https://github.com/dvsa/olcs-selfserve/commit/b07363c397b665c1247c6f8e7723acebbca585f7))

### 4.0.11	2016-12-12

- Version 4.0.11 is the second version of the OLCS Self-serve to be published to Githab.  It contains enhancements to various features and remediation of defects discovered during the first three weeks of the Public Beta delivery.

Reference can be made to the composite release note provided.
