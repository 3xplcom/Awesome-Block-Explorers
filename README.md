Awesome Block Explorers
=======================

What's this list?
-----------------

One of the main goals of crypto is decentralization, and we want this to be applicable to block explorers as well!

Wouldn't be it useful for a block explorer user to be able to cross-check their transaction on another explorer?
We thought it would, so we've decided to come up with this list of good explorers for multiple chains.
Not only it is good for decentralization, but it also allows users to find some extra details on other explorers.

We currently integrate this list on [3xpl.com](https://3xpl.com) (see [example transaction](https://3xpl.com/bitcoin/transaction/4a5e1e4baab89f3a32518a88c31bc87f618f76673e2cc77ab2127b7afdeda33b)).

How to add my favourite explorer?
---------------------------------

New explorers should be added to the `Explorers/` folder in JSON format. Just follow the existing examples.

Note that all blockchains should be present in `Chains/`. If your explorer provides data for a chain we don't yet support, please add a chain JSON file as well.
`foreign_ids` are coin ids (URL slugs) on the following websites: CoinGecko ([example](https://www.coingecko.com/en/coins/bitcoin)), CoinMarketCap ([example](https://coinmarketcap.com/currencies/bitcoin/)), and Binance ([example](https://www.binance.com/en/price/bitcoin)). Don't forget to add examples!

License
-------

This list is released under the terms of the MIT license.
See [LICENSE.md](LICENSE.md) for more information.

By contributing to this repository, you agree to license your work under the MIT license.
Any work contributed where you are not the original author must contain its license header with the original author and source.
