Antragsgrün
===========

The Online Motion Administration/Facilitator for Associations Conventions, General Assemblies and Party Conventions.

Antragsgrün offers a clear and efficient tool for the effective administration of motions, amendments and candidacies: from submission to administration and print template.

A number of organisations are already using the tool successfully such as the federal association of the German Green Party or the German Federal Youth Council. It can be easily adapted to a variety of scenarios.

Core functions:
- Submit motions, proposals and discussion papers online
- Clear amendment
- Submitted amendments are displayed directly in the relevant text section.
- Discuss motions
- Sophisticated administration tools
- Diverse export options
- Great flexibility - it adapts to a lot of different use cases
- Technically mature, data privacy-friendly


Installation using the pre-bundled package
------------------------------------------

Requirements:
- A MySQL-database
- A fully configured web server running PHP

Installation:
- Download the latest package of Antragsgrün: [antragsgruen-3.5.1.tar.bz2](https://www.hoessl.eu/antragsgruen/antragsgruen-3.5.1.tar.bz2)
- Extract the contents into your web folder
- Access the "antragsgruen/"-folder of your web server, e.g. if you extracted the package into the web root of your host named www.example.org/, then access www.example.org/antragsgruen/
- Use the web-based installer to configure the database and further settings


Installation
------------


Required Software (Debian Linux):
```bash
# Using PHP7-packages from [DotDeb](https://www.dotdeb.org/instructions/):
apt-get install php7.0 php7.0-cli php7.0-fpm php7.0-intl php7.0-json php7.0-mcrypt \
                php7.0-mysql php7.0-opcache php7.0-curl php7.0-xml php7.0-mbstring php7.0-zip

# Using PHP5-packages from Debian:
apt-get install php5-cli php5-fpm php5-mysqlnd php5-mcrypt php5-intl php5-curl
```

Install the sources and dependencies from the repository:
```bash
git clone https://github.com/CatoTH/antragsgruen.git
cd antragsgruen
curl -sS https://getcomposer.org/installer | php
./composer.phar global require "fxp/composer-asset-plugin:1.2.2"
./composer.phar install --prefer-dist
npm install
npm run build
```

If you want to use the web-based installer (recommended):
```bash
touch config/INSTALLING
```

If you don't want to use the web-based installer:
```bash
cp config/config.template.json config/config.json
vi config/config.json # you're on your own now :-)
```

Set the permissions (example for Debian Linux):
```bash
sudo chown -R www-data:www-data web/assets
sudo chown -R www-data:www-data runtime
sudo chown -R www-data:www-data config #Can be skipped if you don't use the Installer
```

Set the permissions (example for Mac OS X):
```bash
sudo chown -R _www:_www web/assets
sudo chown -R _www:_www runtime
sudo chown -R _www:_www config #Can be skipped if you don't use the Installer
```

Set up the virtual host of your web server. Example files are provided here:
* Example configuration for [nginx](docs/nginx.sample_single_site.conf)
* Example configuration for [apache](docs/apache.sample.conf)



Developing custom themes
------------------------

You can develop a custom theme using SASS/SCSS for Antragsgrün using the following steps:
* Create a file ```web/css/layout-my-layout.scss``` using layout-classic.scss as a template
* Adapt the SCSS variables and add custom styles
* Run ```gulp``` to compile the SCSS into CSS
* Add a line ```'layout-my-layout' => 'My cool new layout'``` to the "localLayouts"-object in config/config.json
* Now, you can choose your new theme in the consultation settings

A hint regarding the AGPL license and themes: custom stylesheets and images and changes to the standard stylesheets of
Antragsgrün do not have to be redistributed under an AGPL license like other changes to the Antragsgrün codebase.


LaTeX/XeTeX-based PDF-rendering:
--------------------------------

Necessary packets on Linux (Debian):
```bash
apt-get install texlive-lang-german texlive-latex-base texlive-latex-recommended \
                texlive-latex-extra texlive-humanities texlive-fonts-recommended \
                texlive-xetex poppler-utils
```

Necessary packets on Mac OS X:
* [MacTeX](http://www.tug.org/mactex/)
* Poppler ([Homebrew](http://brew.sh/)-Package)


Command Line Commands
---------------------

Force a new password for an user:
```bash
./yii admin/set-user-password user@example.org mynewpassword
```


Developing
----------

You can enable debug mode by creating an empty file config/DEBUG.

To compile the JavaScript- and CSS-Files, you need to install Gulp:
```bash
npm install # Installs all required packages 

npm run build # Compiles the regular JS/CSS-files
npm run watch # Listens for changes in JS/CSS-files and compiles them immediatelly
```

After updating the source code from git, do:
```bash
./composer.phar update
./yii migrate
gulp
```

Testing
-------

* Create a separate (MySQL-)database for testing
* Set up the configuration file: ```
cp config/config_tests.template.json config/config_tests.json && vi config/config_tests.json```
* For the automatical HTML validation, Java needs to be installed and the vnu.jar file from the [Nu Html Checker](https://validator.github.io/validator/) located at /usr/local/bin/vnu.jar.
* For the automatical accessibility validation, [Pa11y](http://pa11y.org/) needs to be installed.
* Start PhantomJS: ```
npm run test:phantomjs --webdriver=4444```
* Start debug server: ```
npm run test:server```
* Run all acceptance tests: ```
run run test:acceptance```
* Run all unit tests: ```
run run test:unit```
* Run a single acceptence-test: ```
npm run test:acceptance MotionCreateCept```
