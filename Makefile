install:
	composer install

lint:
	composer run-script phpcs -- --standard=PSR12 bin src tests -np

lint-fix:
	composer run-script phpcbf -- --standard=PSR12 bin src tests

analyze:
	composer run-script phpstan analyze -- -l max src bin

test:
	composer run-script phpunit tests
