<?php

namespace App\Http\Livewire;

use Livewire\Component;

use App\AbAccount;
use App\Company;

class AccountingTrialBalanceComponent extends Component
{
    public $abAccounts;
    public $company;

    public function render()
    {
        $this->abAccounts = AbAccount::all();
        $this->company = Company::first();

        return view('livewire.accounting-trial-balance-component');
    }
}
