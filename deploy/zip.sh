#!/bin/sh

cd "$(dirname "$0")" || exit

# replace hyvor/blogs:latest with hyvor/blogs:<version> in compose file
if [ -f VERSION.txt ]; then
    VERSION=$(cat VERSION.txt)
    sed -i "s|hyvor/blogs:latest|hyvor/blogs:$VERSION|g" compose.yaml
fi

tar -czvf deploy.tar.gz --transform='s|^|deploy/|' compose.yaml .env VERSION.txt
