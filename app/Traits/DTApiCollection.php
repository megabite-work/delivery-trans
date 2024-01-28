<?php
namespace App\Traits;

trait DTApiCollection
{
    public function paginationInformation($request, $paginated, $default)
    {
        unset(
            $default['links'],
            $default['meta']['links'],
            $default['meta']['path'],
        );
        return $default;
    }
}
