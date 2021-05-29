#!/bin/bash
set -e
protoc --proto_path=protobuf/protos --php_out=protobuf/compile protobuf/protos/*.proto
