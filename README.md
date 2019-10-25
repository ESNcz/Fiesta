# Fiesta system
The goal of this project is to develop a new information system for the non-profit organization called Erasmus Student Network (ESN). ESN is a Europe-wide international student organization coordinating student exchange programs. It implements solutions that encourage the higher level integration of new international students. 

![Screenshot](https://i.imgur.com/8OheiMU.png)

## Instalation

The best way to install this project is using Composer. If you don't have Composer yet, download it following [the instructions](https://doc.nette.org/composer). Then use command:

### Stable version

```bash
git https://github.com/thanhdolong/FiestaSystem.git
cd Fiesta/
composer install
```

Setup configuration options for Fiesta system in `config.dev.neon` and setup database (folder `database`). Make directories `temp/` and `log/` writable.

Web Server Setup
----------------

The simplest way to get started is to start the built-in PHP server in the root directory of your project:

	php -S localhost:8000 -t www

Then visit `http://localhost:8000` in your browser to see the welcome page.

For Apache or Nginx, setup a virtual host to point to the `www/` directory of the project and you
should be ready to go.

It is CRITICAL that whole `app/`, `log/` and `temp/` directories are not accessible directly via a web browser. See [security warning](https://nette.org/security-warning).

### Requirements
- Core Fiesta requires at least PHP 5.6

### Notice: Composer PHP version

This project forces PHP 5.6 as your PHP version for Composer packages. If you have newer
version on production server you should change it in `composer.json`:

```json
"config": {
	"platform": {
		"php": "7.2"
	}
}
```

### Learn More

To learn the using of Fiesta by more examples, take a look at the [Documentation](http://fiesta.esncz.org/help). There we summarized most common tasks in Fiesta, you can get a better idea on what system can do.

## Examples

We are working on several examples. Here is some to get you started:

* [Plugin written in React](https://github.com/d-kozak/fiesta-plugin-react).
Demo od this project can be find [here](https://fiesta-plugin-react.netlify.com/).
* [Plugin written in React natice](https://bit.ly/2MKhzDW)


## Contributing

The main purpose of this repository is to continue to evolve Fiesta core. Development of Fiesta happens in the open on GitHub, and we are grateful to the community for contributing bugfixes and improvements.

### Future of Core Fiesta

I want to keep Core Fiesta lightweight. System will primary focus on providing a simple solution for authentization of users from Fiesta and creating new modules. This system is an attempt to create a platform capable of embracing all of the specific needs of the various ESN sections.

The actual version of the system implements several modules: buddy system module, pickup system module, event manager etc.


### Contact

 If you find an issue, just [open a ticket](https://github.com/thanhdolong/Fiesta/issues/new/choose). Pull requests are warmly welcome as well.

### Author

[Thành Đỗ Long](https://github.com/thanhdolong), thanh.dolong@gmail.com

### License

Fiesta system is released under the AGPL-3.0 license. See LICENSE for details.