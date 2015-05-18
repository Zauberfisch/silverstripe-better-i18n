# SilverStripe i18n replacement

## Work in progress

This module is still work in progress and a hack-ish replacement for the
SilverStripe i18n (currently only I18nTextCollector).
This module exists because of current limitations in SilerStripe and might 
change its API or be completely obsolete once i18n is re-factored.

## Current Features

- Improved Text Collection 
	- merge with existing lang file
	- will also collect db_, has_many:, ... entities
	- Better task output (displays what is untranslated, what is new and what has been removed)
	- untranslated strings are prefixed with `__` (will probably change to yml comment when I figure out how to)

## Installation

#### Requirements

- php needs write permissions to the lang folder(s)

#### Installing the module

1. Install the module using composer or download and place in your project folder
2. Run `?flush=1`

## Usage

#### BetterI18NTextCollectorTask

Parameters:

- `targetlocale` the locale for with the lang file should be created    
	eg: `en` or a list of locales: `en,de,fr`    
	default: `en`
- `module` the module to translate (will write to `module/lang/$targetlocale.yml`)
	eg: `mysite`, `framework`, `mysite,framework` or `themes/mytheme`
	default: `mysite`

Examples:

- `http://mysite.com/dev/tasks/BetterI18nTextCollectorTask?flush=1`
- `http://mysite.com/dev/tasks/BetterI18nTextCollectorTask?targetlocale=de,en&module=mysite&flush=1`
- With sake/cli: `sake dev/tasks/BetterI18nTextCollectorTask flush=1`
- With sake/cli: `sake dev/tasks/BetterI18nTextCollectorTask "targetlocale=de,en&module=mysite&flush=1"`

Warning: running the `BetterI18nTextCollectorTask` will overwrite your existing lang file. 
It will merge existing entities in the file, but will delete unused ones.

## License

	Copyright (c) 2015, Zauberfisch
	All rights reserved.

	Redistribution and use in source and binary forms, with or without
	modification, are permitted provided that the following conditions are met:
		* Redistributions of source code must retain the above copyright
		  notice, this list of conditions and the following disclaimer.
		* Redistributions in binary form must reproduce the above copyright
		  notice, this list of conditions and the following disclaimer in the
		  documentation and/or other materials provided with the distribution.
		* Neither the name Zauberfisch nor the names of other contributors may 
		  be used to endorse or promote products derived from this software 
		  without specific prior written permission.

	THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
	ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
	WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
	DISCLAIMED. IN NO EVENT SHALL <COPYRIGHT HOLDER> BE LIABLE FOR ANY
	DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
	(INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
	LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND
	ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
	(INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
	SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
