### create new working directory
if [ -d dealtmodule ]; then rm -rf dealtmodule; fi
mkdir dealtmodule

### copy necessary module files
echo "🗂     Copying repository files.."
cp -R config dealtmodule/config
cp -R controllers dealtmodule/controllers
cp -R resources dealtmodule/resources
cp -R src dealtmodule/src
cp -R views dealtmodule/views
cp composer.json dealtmodule/composer.json
cp config.xml dealtmodule/config.xml
cp dealtmodule.php dealtmodule/dealtmodule.php
cp logo.png dealtmodule/logo.png

### clean-up unnecessary files
rm -rf dealtmodule/resources/scripts
rm -rf dealtmodule/views/node_modules

### composer install & dump autoload
### with prestashop specifics
echo "🐘    Running composer scripts.."
cd dealtmodule
{
    composer install --no-dev
    composer dump-autoload -o --no-dev
} &>/dev/null
echo "✅    Composer install & dump-autoload successful"

cd ../
echo "🤐    Creating module zip file.."
VERSION=$(git tag | sort -g | tail -1)
zip -r dealtmodule_$VERSION.zip dealtmodule &>/dev/null
rm -rf dealtmodule

echo "✅    Archive created : dealtmodule_$VERSION.zip"
