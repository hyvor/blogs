<?php
namespace App\Data\Enums;

enum UserRoleEnum: string {

    CASE OWNER = 'owner';
    CASE ADMIN = 'admin';
    CASE EDITOR = 'editor';
    CASE WRITER = 'writer';
    CASE CONTRIBUTOR = 'contributor';
    CASE FINANCE = 'finance';

}