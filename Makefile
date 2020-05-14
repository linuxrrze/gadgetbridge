
all: dev-setup lint build-js-production test

# Dev env management
dev-setup: clean clean-dev npm-init

npm-init:
	yarn ci

npm-update:
	yarn update

# Building
build-js:
	yarn run dev

build-js-production:
	yarn run build

watch-js:
	yarn run watch

# Testing
test:
	yarn run test

test-watch:
	yarn run test:watch

test-coverage:
	yarn run test:coverage

# Linting
lint:
	yarn run lint

lint-fix:
	yarn run lint:fix

# Style linting
stylelint:
	yarn run stylelint

stylelint-fix:
	yarn run stylelint:fix

# Cleaning
clean:
	rm -f js/*

clean-dev:
	rm -rf node_modules