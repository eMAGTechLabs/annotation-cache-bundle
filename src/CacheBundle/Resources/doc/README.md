In order to have caching on methods:

1. add @Cache  annotation to the methods to be cached


    @Cache(cache="some_sort_of_prefix", [key="<name of argument to include in cache key>"], [ttl=300], [reset=true])