# simple-php-framework

To run the project, you just need to create a .env file using the example .env.dist file and start Docker in the project's root directory using the following command:

#### - docker-compose up -d

It contains the default "URL shortener" project. 

By default, the project uses files to save url-code pairs.

If you want to use a database instead of files for the default project provided with the framework, you need to change "config/http.php" to

#### IUrlCodePairRepository::class => [
    ServiceConfigTypeKeys::CLASSNAME => DBUrlCodePairRepository::class,
#### ]

And then run the following command inside the PHP container for creating database using migrations
#### - bin/console migrate