<?php

use Jelix\Acl2Db\Command;

$application->add(new Command\Acl2\RightsList());
$application->add(new Command\Acl2\AddRight());
$application->add(new Command\Acl2\ForbidRight());
$application->add(new Command\Acl2\RemoveRight());
$application->add(new Command\Acl2\SubjectList());
$application->add(new Command\Acl2\SubjectCreate());
$application->add(new Command\Acl2\SubjectDelete());
$application->add(new Command\Acl2\SubjectGroupList());
$application->add(new Command\Acl2\SubjectGroupCreate());
$application->add(new Command\Acl2\SubjectGroupDelete());

$application->add(new Command\Acl2Groups\GroupsList());
$application->add(new Command\Acl2Groups\GroupCreate());
$application->add(new Command\Acl2Groups\GroupDelete());
$application->add(new Command\Acl2Groups\GroupName());
$application->add(new Command\Acl2Groups\GroupDefault());

$application->add(new Command\Acl2Users\UsersList());
$application->add(new Command\Acl2Users\UserRegister());
$application->add(new Command\Acl2Users\UserUnregister());
$application->add(new Command\Acl2Users\UserAddGroup());
$application->add(new Command\Acl2Users\UserRemoveGroup());
