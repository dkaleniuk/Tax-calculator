# Tax calculator
An application that can calculate tax for the transactions provided through the file (input.txt)


# How to run the app
0. Be sure that you have **php 8.2** installed.
1. Get the composer file 

   `sh composer.installer.sh`
2. Run **composer.phar update** to install dependencies.

3. Create a **.env** file from the **.env.dist** file:

   `cp .env.dist .env`

4. Paste **API_LAYER_KEY** into **.env** file.

   You can generate **API_LAYER_KEY** here: https://apilayer.com/docs/article/managing-api-keys.

5. Run the app **php bin/console calculate-tax input.txt**

   Note: you need to provide an input.txt file with the transactions' data.
   
   For further information, you can check the file example below.


# Input file example (input.txt)

{"bin":"45717360","amount":"100.00","currency":"EUR"}


{"bin":"516793","amount":"50.00","currency":"USD"}


{"bin":"45417360","amount":"10000.00","currency":"JPY"}


{"bin":"41417360","amount":"130.00","currency":"USD"}


{"bin":"4745030","amount":"2000.00","currency":"GBP"}


# How to run tests
1. Run **php composer.phar test**.

   Note: **.env** file should be created to run tests.

   To run the tests you can insert any dummy data for **API_LAYER_KEY** in the .env file.

   Example: **API_LAYER_KEY=dummy_value**
   Dependencies are mocked.
