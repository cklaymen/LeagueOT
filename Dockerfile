FROM ubuntu:18.04

WORKDIR /usr/app/src

RUN apt update

RUN apt install -y cmake build-essential libluajit-5.1-dev libmysqlclient-dev libboost-system-dev libboost-iostreams-dev libboost-filesystem-dev libpugixml-dev libcrypto++-dev

RUN apt-get update

RUN apt-get install -y autoconf libboost-all-dev libgmp-dev liblua5.1-0 liblua5.1-0-dev liblua50 liblua50-dev liblualib50 liblualib50-dev lua50 lua5.1 libxml2-dev libxml++2.6-dev libmysql++-dev libsqlite0-dev libsqlite3-dev build-essential

COPY ./src ./src
COPY ./cmake ./cmake
COPY ./CMakeLists.txt ./CMakeLists.txt

RUN mkdir build

RUN cd build && cmake .. && make

RUN cp build/tfs tfs

COPY ./data ./data

COPY ./config.lua ./config.lua

CMD ./tfs
