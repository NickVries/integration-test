<?php

declare(strict_types=1);

namespace App\Shipments\Domain\Item;

use JetBrains\PhpStorm\ArrayShape;

class ItemFactory
{
    public function createFromArray(
        #[ArrayShape([
            'Description'   => 'string',
            'GrossWeight'   => 'string',
            'NetWeight'     => 'float',
            'NetWeightUnit' => 'string',
            'PictureUrl'    => 'string',
        ])]
        array $item,
    ): Item {
        $netWeight = empty($item['NetWeight']) ? null : (float) $item['NetWeight'];
        $weight = empty($item['GrossWeight']) ? $netWeight : (float) $item['GrossWeight'];

        return new Item(
            (string) $item['Description'],
            Weight::createFromUnit($weight, (string) $item['NetWeightUnit']),
            $item['PictureUrl'],
        );
    }

    public function createNullItem(): Item
    {
        return new NullItem();
    }
}
