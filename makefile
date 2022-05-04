STAGING_STACK_NAME=StagingStack
STAGING_BUCKET_NAME=`aws cloudformation describe-stacks --stack-name ${STAGING_STACK_NAME} --query "Stacks[0].Outputs[?OutputKey=='PrestaShopModuleApplicationBucketName'].OutputValue" --output text`

install:
	npm ci

build:
	npm run build

dev:
	npm start

bundle_ci: install build

deploy_staging:
	aws s3 sync dist s3://${STAGING_BUCKET_NAME} --exclude "index.html" --exclude "robots.txt" --exclude ".well-known" --cache-control max-age=31536000,public
	aws s3 cp dist/index.html s3://${STAGING_BUCKET_NAME}/index.html --metadata-directive REPLACE --cache-control max-age=0,no-cache,no-store,must-revalidate --content-type text/html --acl public-read 
