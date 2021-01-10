<?php

declare(strict_types=1);

namespace App\Shipments\Domain\Item;

use JetBrains\PhpStorm\ArrayShape;
use function dd;

class ItemFactory
{
    public function createFromArray(
        #[ArrayShape([
            'Description'   => 'string',
            'NetWeight'     => 'float',
            'NetWeightUnit' => 'string',
            'PictureUrl'    => 'string',
        ])]
        array $item
    ): Item {
        return new Item(
            (string) $item['Description'],
            Weight::createFromUnit((float) $item['NetWeight'], (string) $item['NetWeightUnit']),
            $item['PictureUrl'],
        );
    }

    public function createNullItem(): Item
    {
        return new NullItem();
    }
}
