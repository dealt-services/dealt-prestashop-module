### Make sure to expose the _PS_ROOT_DIR_ variable in your
### .zshrc/.bashrc 

csfix:
	php vendor/bin/php-cs-fixer fix;

phpstan:
	vendor/bin/phpstan analyse --configuration=./phpstan.neon --memory-limit 512M;

publish:
	./resources/scripts/publish.sh;

npm-install:
	cd views && npm install;

npm-build:
	rm -rf views/public;
	cd views && npm run build;

npm-watch:
	cd ./views && npm run watch;
