# Store Pickup GraphQl

**Store Pickup GraphQl is a part of MageINIC Store Pickup extension that adds GraphQL features.** This extension extends Store Pickup definitions.

## 1. How to install

Run the following command in Magento 2 root folder:

```
composer require mageinic/store-pickup-graphql

php bin/magento maintenance:enable
php bin/magento setup:upgrade
php bin/magento setup:di:compile
php bin/magento setup:static-content:deploy
php bin/magento maintenance:disable
php bin/magento cache:flush
```

**Note:**
Magento 2 Store Pickup GraphQL requires installing [MageINIC Store Pickup](https://github.com/mageinic/Store-Pickup) in your Magento installation.

**Or Install via composer [Recommend]**
```
composer require mageinic/store-pickup
```

## 2. How to use

- To view the queries that the **MageINIC Store Pickup GraphQL** extension supports, you can check `Store Pickup GraphQl User Guide.pdf` Or run `Store Pickup Graphql.postman_collection.json` in Postman.

## 3. Get Support

- Feel free to [contact us](https://www.mageinic.com/contact.html) if you have any further questions.
- Like this project, Give us a **Star**