Building assets
===============


#### Requirements

* Install nodejs :
    * with [binaries](https://nodejs.org/en/download/)
    * or the packet manager for your Linux distribution (e.g. Ubuntu : `sudo apt install nodejs`)
* Install dependencies :
    * `cd assets/`
    * `npm install`

It creates a `assets/node_modules/` directory. Don't commit it into the git repository!


#### Installation

* Build for production (minified JS files) : `npm run build`

* Build for development 

  - `npm run dev`: source mapping, no minimization
  - `npm run watch`: source mapping, build is executed at every change on a JS file



Look at [webpack documentation](https://webpack.js.org/guides/development/) for other development options (e.g. live reloading)

#### Lint check

Run `npm run lint-check` to see if you have some syntax error.
