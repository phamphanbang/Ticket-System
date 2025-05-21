<?php 

namespace App\Constants;

enum PaginateConstant: int
{
    case DEFAULT_PER_PAGE = 10;
    case DEFAULT_OFFSET = 0;
    case DEFAULT_PAGE = 1;
}