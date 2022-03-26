<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\DB;

use App\Customer;
use App\SaleInvoice;
use App\SaleInvoicePaymentType;
use App\SaleInvoicePayment;
use App\SaleInvoiceAddition;
use App\SaleInvoiceAdditionHeading;

use App\JournalEntry;
use App\JournalEntryItem;
use App\AbAccount;
use App\LedgerEntry;

class SeatTableWorkDisplayMakePayment extends Component
{
    public $seatTable;

    public $total;
    public $pay_by;
    public $tender_amount;

    public $discount = 0;
    public $service_charge = 0;
    public $grand_total;

    public $returnAmount;

    /* Customer to which sale_invoice will be made */
    public $customer;

    /* Customer info */
    public $customer_name;
    public $customer_phone;
    public $customer_address;
    public $customer_pan;

    /* Sale invoice addition headings */
    public $saleInvoiceAdditionHeadings;

    /* Sale invoice additions */
    public $saleInvoiceAdditions = array();

    public $saleInvoicePaymentTypes;
    public $sale_invoice_payment_type_id;

    public $modes = [
        'paid' => false,
        'customer' => false,
    ];

    protected $listeners = [
      'makePaymentPleaseUpdate' => 'mount',
    ];

    public function mount()
    {
        $this->saleInvoicePaymentTypes = SaleInvoicePaymentType::all();

        $this->saleInvoiceAdditionHeadings = SaleInvoiceAdditionHeading::all();

        foreach (SaleInvoiceAdditionHeading::all() as $saleInvoiceAddition) {
            $this->saleInvoiceAdditions += [$saleInvoiceAddition->name => 0];
        }

        $this->total = $this->seatTable->getCurrentBookingTotalAmount();
        $this->grand_total = $this->seatTable->getCurrentBookingGrandTotalAmount();
    }

    public function render()
    {
        return view('livewire.seat-table-work-display-make-payment');
    }

    public function updatedSaleInvoiceAdditions()
    {
      // dd ('WOW');
      $this->calculateGrandTotal();
    }

    /* Clear modes */
    public function clearModes()
    {
        foreach ($this->modes as $key => $val) {
            $this->modes[$key] = false;
        }
    }

    /* Enter and exit mode */
    public function enterMode($modeName)
    {
        $this->clearModes();

        $this->modes[$modeName] = true;
    }

    public function exitMode($modeName)
    {
        $this->modes[$modeName] = false;
    }

    public function store()
    {
        $validatedData = $this->validate([
            'tender_amount' => 'required|integer',
            'sale_invoice_payment_type_id' => 'required|integer',
        ]);

        if ($this->modes['customer']) {
            $validatedData2 = $this->validate([
                'customer_name' => 'required',
                'customer_phone' => 'required',
                'customer_address' => 'nullable',
                'customer_pan' => 'nullable',
            ]);
        }

        /* Get the sale_invoice */
        $saleInvoice = $this->seatTable->getCurrentBooking()->saleInvoice;

        /* Final Payment Status */
        $finalPaymentStatus = $saleInvoice->payment_status;

        /* Calculate the grand_total */
        $this->calculateGrandTotal();

        /* Get current booking/invoice amount */
        $currentBookingAmount = $this->seatTable->getCurrentBookingPendingAmount();
        $currentBookingGrandAmount = $this->seatTable->getCurrentBookingGrandTotalAmount();

        /* If no customer do not take less payments !!! */
        if (! $this->modes['customer'] && $this->tender_amount < $this->grand_total) {
            return;
        }

        DB::beginTransaction();

        /*
         * Do Customer stuff if needed.
         *
         */

        try {
            if ($this->modes['customer']) {
                $customer = Customer::where('phone', $this->customer_phone)->first();
                if (! $customer) {
                    $customer = new Customer;

                    $customer->name = $this->customer_name;
                    $customer->phone = $this->customer_phone;;
                    if ($this->customer_address) {
                        $customer->address = $this->customer_address;;
                    }
                    if ($this->customer_pan) {
                        $customer->pan_number = $this->customer_pan;;
                    }

                    $customer->save();

                    /* Make an ab_account of this customer */
                    $abAccount = new AbAccount;
                    $abAccount->name = $customer->name . ' ' . $customer->phone;
                    $abAccount->save();

                    /* Link the ab_account to the customer */
                    $customer->ab_account_id = $abAccount->ab_account_id;
                    $customer->save();
                }
            }

            /*
             *
             * Todo: Make payment against an invoice
             *
             */


            if ($this->modes['customer']) {
                $saleInvoice->customer_id = $customer->customer_id;
                $saleInvoice->save();
            }

            /* Make Sale Invoice Additions if needed. */
            foreach ($this->saleInvoiceAdditions as $key => $val) {
                if ($val > 0) {
                    $saleInvoiceAdditionHeading = SaleInvoiceAdditionHeading::where('name', $key)->first();

                    $saleInvoiceAddition = new SaleInvoiceAddition;

                    $saleInvoiceAddition->sale_invoice_id = $saleInvoice->sale_invoice_id;
                    $saleInvoiceAddition->sale_invoice_addition_heading_id = $saleInvoiceAdditionHeading->sale_invoice_addition_heading_id;
                    $saleInvoiceAddition->amount = $val;

                    $saleInvoiceAddition->save();
                }
            }

            /* If payment received then create a payment record. */
            if ($this->tender_amount > 0) {
                /* Make sale_invoice_payment */
                $saleInvoicePayment = new SaleInvoicePayment;

                $saleInvoicePayment->sale_invoice_payment_type_id = $validatedData['sale_invoice_payment_type_id'];

                $saleInvoicePayment->payment_date = date('Y-m-d');
                $saleInvoicePayment->sale_invoice_id = $saleInvoice->sale_invoice_id;

                if ($this->tender_amount < $this->grand_total) {
                    $saleInvoicePayment->amount = $this->tender_amount;
                    $this->returnAmount = 0;
                    $finalPaymentStatus = 'partially_paid';
                } else {
                    $saleInvoicePayment->amount = $this->grand_total;
                    $this->returnAmount = $this->tender_amount - $this->grand_total;
                    $finalPaymentStatus = 'paid';
                }

                $saleInvoicePayment->save();
            }


            /* Update payment_status of sale invoice */
            $saleInvoice->payment_status = $finalPaymentStatus;
            $saleInvoice->save();


            $booking = $this->seatTable->getCurrentBooking();
            $booking->status = 'closed';
            $booking->save();

            /* Make journal entry */
            $this->makeAccountingEntry($saleInvoice);

            DB::commit();

            $this->enterMode('paid');
        } catch (\Exception $e) {
            DB::rollback();
            dd ($e);
            session()->flash('errorDbTransaction', 'Some error in DB transaction.');
        }
    }

    public function finishPayment()
    {
        // $booking = $this->seatTable->getCurrentBooking();
        // if ($booking) {
        //     $booking->status = 'closed';
        //     $booking->save();
        // }

        $this->emit('exitMakePaymentMode');
    }

    public function fetchCustomerData()
    {
        $customer = Customer::where('phone', $this->customer_phone)->first();

        if ($customer) {
            $this->customer = $customer;

            $this->customer_name = $customer->name;
            $this->customer_address = $customer->address;
        }
    }

    public function calculateGrandTotal()
    {
        /* TODO
        $validatedData = $this->validate([
            'discount' => 'required|integer',
            'service_charge' => 'required|integer',
        ]);
        */

        $this->grand_total = $this->total;

        foreach ($this->saleInvoiceAdditions as $key => $val) {
            if (strtolower(SaleInvoiceAdditionHeading::where('name', $key)->first()->effect) == 'plus') {
                if (is_numeric($val)) {
                    $this->grand_total += $val;
                }
            } else if (strtolower(SaleInvoiceAdditionHeading::where('name', $key)->first()->effect) == 'minus') {
                if (is_numeric($val)) {
                    $this->grand_total -= $val;
                }
            } else {
                dd('Sale invoice addition heading configurations gone wrong! Contact your service provider.');
            }
        }
    }

    public function makeAccountingEntry($saleInvoice)
    {
        $journalEntry = new JournalEntry;
        $journalEntry->date = date('Y-m-d');
        $journalEntry->notes = 'Sales made';
        $journalEntry->save();

        if ($saleInvoice->payment_status == 'paid') {

            /*
             * Cash account debit
             */

            $journalEntryItem = new JournalEntryItem;

            $journalEntryItem->journal_entry_id = $journalEntry->journal_entry_id;
            $journalEntryItem->ab_account_id = AbAccount::where('name', 'cash')->first()->getKey();
            $journalEntryItem->type = 'debit';
            $journalEntryItem->amount = $saleInvoice->getTotalAmount();

            $journalEntryItem->save();


            /*
             * Sales account credit
             */

            $journalEntryItem = new JournalEntryItem;

            $journalEntryItem->journal_entry_id = $journalEntry->journal_entry_id;
            $journalEntryItem->ab_account_id = AbAccount::where('name', 'sales')->first()->getKey();
            $journalEntryItem->type = 'credit';
            $journalEntryItem->amount = $saleInvoice->getTotalAmount();

            $journalEntryItem->save();
        } else if ($saleInvoice->payment_status == 'partially_paid') {
            /*
             * Cash account debit
             */

            $journalEntryItem = new JournalEntryItem;

            $journalEntryItem->journal_entry_id = $journalEntry->journal_entry_id;
            $journalEntryItem->ab_account_id = AbAccount::where('name', 'cash')->first()->getKey();
            $journalEntryItem->type = 'debit';
            $journalEntryItem->amount = $saleInvoice->getPaidAmount();

            $journalEntryItem->save();

            /*
             * Customer account debit
             *
             * account receivable
             *
             */

            $journalEntryItem = new JournalEntryItem;

            $journalEntryItem->journal_entry_id = $journalEntry->journal_entry_id;

            $journalEntryItem->ab_account_id = $saleInvoice->customer->abAccount->ab_account_id;

            $journalEntryItem->type = 'debit';
            $journalEntryItem->amount = $saleInvoice->getPendingAmount();

            $journalEntryItem->save();

            /*
             * Sales account credit
             */

            $journalEntryItem = new JournalEntryItem;

            $journalEntryItem->journal_entry_id = $journalEntry->journal_entry_id;
            $journalEntryItem->ab_account_id = AbAccount::where('name', 'sales')->first()->getKey();
            $journalEntryItem->type = 'credit';
            $journalEntryItem->amount = $saleInvoice->getTotalAmount();

            $journalEntryItem->save();
        } else if ($saleInvoice->payment_status == 'pending') {

            /*
             * Customer account debit
             *
             * account receivable
             *
             */

            $journalEntryItem = new JournalEntryItem;

            $journalEntryItem->journal_entry_id = $journalEntry->journal_entry_id;

            $journalEntryItem->ab_account_id = $saleInvoice->customer->abAccount->ab_account_id;

            $journalEntryItem->type = 'debit';
            $journalEntryItem->amount = $saleInvoice->getTotalAmount();

            $journalEntryItem->save();


            /*
             * Sales account credit
             */

            $journalEntryItem = new JournalEntryItem;

            $journalEntryItem->journal_entry_id = $journalEntry->journal_entry_id;
            $journalEntryItem->ab_account_id = AbAccount::where('name', 'sales')->first()->getKey();
            $journalEntryItem->type = 'credit';
            $journalEntryItem->amount = $saleInvoice->getTotalAmount();

            $journalEntryItem->save();
        } else {
          dd('Whoops');
        }

        $this->makeLedgerEntry($journalEntry);
    }

    public function createPersonalAccount($name)
    {
        $abAccount = new AbAccount;

        $abAccount->name = $name;

        $abAccount->save();

        return $abAccount->getKey();
    }

    public function makeLedgerEntry($journalEntry)
    {
        /* Find single side and multiple sides */
        $debitCount = 0;
        $creditCount = 0;
        foreach ($journalEntry->journalEntryItems as $journalEntryItem) {
            if ($journalEntryItem->type == 'debit') {
                $debitCount++;
            } else if ($journalEntryItem->type == 'credit') {
                $creditCount++;
            } else {
                dd('Whoops');
            }
        }

        $multiSide = '';
        if ($debitCount > 1 && $creditCount > 1) {
            dd('Whoops');
        } else if ($debitCount > 1) {
            $multiSide = 'debit';
        } else if ($creditCount > 1) {
            $multiSide = 'credit';
        }

        foreach ($journalEntry->journalEntryItems as $journalEntryItem) {

            foreach ($journalEntry->journalEntryItems as $jei ) {
                if ($jei->journal_entry_item_id == $journalEntryItem->journal_entry_item_id) {
                    continue;
                }

                if ($jei->type == $journalEntryItem->type) {
                    continue;
                }

                $ledgerEntry = new LedgerEntry;

                $ledgerEntry->date = $journalEntry->date;
                $ledgerEntry->ab_account_id = $journalEntryItem->ab_account_id;
                $ledgerEntry->journal_entry_id = $journalEntry->journal_entry_id;

                if ($jei->type == 'credit') {
                    $ledgerEntry->particulars = 'To ' . $jei->abAccount->name . ' A/c';
                } else if ($jei->type == 'debit') {
                    $ledgerEntry->particulars = 'By ' . $jei->abAccount->name . ' A/c';
                } else {
                    dd('Whoops');
                }

                $ledgerEntry->related_ab_account_id = $jei->abAccount->ab_account_id;
                $ledgerEntry->type = $journalEntryItem->type;

                if ($journalEntryItem->type == $multiSide) {
                    $ledgerEntry->amount = $journalEntryItem->amount;
                } else {
                    $ledgerEntry->amount = $jei->amount;
                }

                $ledgerEntry->save();
            }
        }
    }
}
