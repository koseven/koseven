Kohana auth module
---
| ver   | Stable                                                                                                                       | Develop                                                                                                                        |
|-------|------------------------------------------------------------------------------------------------------------------------------|--------------------------------------------------------------------------------------------------------------------------------|
| 3.3.x | [![Build Status - 3.3/master](https://travis-ci.org/kohana/auth.svg?branch=3.3%2Fmaster)](https://travis-ci.org/kohana/auth) | [![Build Status - 3.3/develop](https://travis-ci.org/kohana/auth.svg?branch=3.3%2Fdevelop)](https://travis-ci.org/kohana/auth) |
| 3.4.x | [![Build Status - 3.4/master](https://travis-ci.org/kohana/auth.svg?branch=3.4%2Fmaster)](https://travis-ci.org/kohana/auth) | [![Build Status - 3.4/develop](https://travis-ci.org/kohana/auth.svg?branch=3.4%2Fdevelop)](https://travis-ci.org/kohana/auth) |

I've forked the main Auth module because there were some fundamental flaws with it:

 1. It's trivial to [bruteforce](http://dev.kohanaframework.org/issues/3163) publicly hidden salt hashes.
    - I've fixed this by switching the password hashing algorithm to the more secure secret-key based hash_hmac method.
 2. ORM drivers were included.
    - I've fixed this by simply removing them. They cause confusion with new users because they think that Auth requires ORM. The only driver currently provided by default is the file driver.
 3. Auth::get_user()'s api is inconsistent because it returns different data types.
    - I've fixed this by returning an empty user model by default. You can override what gets returned (if you've changed your user model class name for instance) by overloading the get_user() method in your application.

These changes should be merged into the mainline branch eventually, but they completely break the API, so likely won't be done until 3.1.