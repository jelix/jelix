#!/bin/sh

prefix=$1
mysql -u test_user -pjelix \
   -e "drop table if exists ${prefix}jacl2_group;drop table if exists ${prefix}jacl2_rights;drop table if exists ${prefix}jacl2_subject;drop table if exists ${prefix}jacl2_subject_group;drop table if exists ${prefix}jlx_cache;drop table if exists ${prefix}jlx_user;drop table if exists ${prefix}jsessions;drop table if exists ${prefix}jacl2_user_group;" \
   testapp;
