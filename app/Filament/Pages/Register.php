<?php

namespace App\Filament\Pages;

use Filament\Auth\Pages\Register as BaseRegister;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;
use App\Models\User;

class Register extends BaseRegister
{
    public function defaultForm(Schema $schema): Schema
    {
        // Ensure the schema knows the model so relationship() fields work.
        return $schema
            ->model(User::class)
            ->statePath('data');
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
              $this->getNameFormComponent(),
              $this->getEmailFormComponent(),
              TextInput::make('pas_membership_no')
                ->label('No. Keahlian PAS')
                ->required()
                ->maxLength(7),
              Select::make('division')
                ->label('Kawasan Keahlian PAS')
                ->options(\App\Models\Kawasan::pluck('name', 'id'))
                ->searchable()
                ->preload()
                ->required(),
              $this->getPasswordFormComponent(),
              $this->getPasswordConfirmationFormComponent(),
            ]);
    }

    protected function handleRegistration(array $data): User
    {
        // In Filament, the password is already hashed by the form component's dehydrateStateUsing.
        $user = User::create([
          'name' => $data['name'],
          'email' => $data['email'],
          'pas_membership_no' => $data['pas_membership_no'],
          'division' => $data['division'] ?? null,
          'password' => $data['password'],
        ]);
        
          
        auth()->login($user);

        // Attach the user to a default team
        //$user->databases()->syncWithoutDetaching([1]);

        return $user;
    }

    protected function afterRegister(): void
    {
        if (! auth()->check()) {
            return;
        }

        $user = auth()->user();

        // now do whatever you need with $user
    }
}
