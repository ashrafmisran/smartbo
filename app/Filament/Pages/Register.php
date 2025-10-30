<?php

namespace App\Filament\Pages;

use Filament\Auth\Pages\Register as BaseRegister;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\FileUpload;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class Register extends BaseRegister
{
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
              $this->getPasswordFormComponent(),
              $this->getPasswordConfirmationFormComponent(),
            ]);
    }

    protected function handleRegistration(array $data): User
    {
        $data = $this->form->getState();

        //dd($data);
        
        $user = User::create([
          'name' => $data['name'],
          'email' => $data['email'],
          'pas_membership_no' => $data['pas_membership_no'],
          'password' => Hash::make($data['password']),
        ]);
        
          
        auth()->login($user);

        // Attach the user to a default team
        $user->databases()->syncWithoutDetaching([1]);

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
