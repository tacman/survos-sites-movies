# Movies

c import:entities App\\Entity\\Movie --file data/movies.csv
c import:entities App\\Entity\\Wam --file data/wam/raw/wam-dywer.csv

# from https://github.com/metmuseum/openaccess
c import:entities MetObject --file ../mus/data/met/json/obj.json

$ git lfs clone https://github.com/metmuseum/openaccess

## To add 

* install symfony/ux-icons
* make:search?  code:search to add #[MeiliIndex()] and populate rawName.html.twig?

Install a bootstrap theme (bootstrap, tabler, bootswatch, etc.)

bin/console importmap:require @tabler/core

# app.js

```js
import 'instantsearch.css/themes/algolia.min.css';
import '@tabler/core';
import '@tabler/core/dist/css/tabler.min.css';

```

