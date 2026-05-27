<?php

namespace App\Filament\Resources\Offers\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class OfferForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('offer_number')
                    ->required(),
                TextInput::make('offer_title')
                    ->required(),
                DatePicker::make('offer_date')
                    ->required(),
                Textarea::make('services')
                    ->columnSpanFull(),
                Textarea::make('works')
                    ->columnSpanFull(),
                Textarea::make('materials')
                    ->columnSpanFull(),
                TextInput::make('total_price')
                    ->required()
                    ->numeric()
                    ->default(0)
                    ->prefix('$'),
                TextInput::make('status')
                    ->required()
                    ->default('portfolio'),
                Textarea::make('custom_sections')
                    ->columnSpanFull(),
                Textarea::make('offer_description')
                    ->columnSpanFull(),
                TextInput::make('crm_deal_id')
                    ->numeric(),
                TextInput::make('customer_name'),
                TextInput::make('customer_nip'),
                TextInput::make('customer_address'),
                TextInput::make('customer_city'),
                TextInput::make('customer_postal_code'),
                TextInput::make('customer_phone')
                    ->tel(),
                TextInput::make('customer_email')
                    ->email(),
                TextInput::make('profit_percent')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('profit_amount')
                    ->required()
                    ->numeric()
                    ->default(0),
                Toggle::make('schedule_enabled')
                    ->required(),
                Textarea::make('schedule')
                    ->columnSpanFull(),
                Textarea::make('payment_terms')
                    ->columnSpanFull(),
                TextInput::make('created_by')
                    ->numeric(),
            ]);
    }
}
