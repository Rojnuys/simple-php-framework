# simple-php-framework

To run the project, you just need to create a .env file using the example .env.dist file and start Docker in the project's root directory using the following command:

#### - docker-compose up -d

If you want to create the database using migrations for the default "URL shortener" project provided with the framework, run the following command inside the PHP container
#### - bin/console migrate