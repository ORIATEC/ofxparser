<?php

namespace OfxParser;

use Exception;
use PHPUnit\Framework\TestCase;
use SimpleXMLElement;

class OfxTest extends TestCase
{
    protected SimpleXMLElement $ofxData;
    private Ofx $ofsContent;

    /**
     * @throws Exception
     */
    public function setUp(): void
    {
        $ofxFile = dirname(__DIR__) . '/fixtures/ofxdata-xml.ofx';
        $this->ofxData = simplexml_load_string(file_get_contents($ofxFile));
        $this->ofsContent = new Ofx($this->ofxData);
    }

    public function testBuildsSignOn(): void
    {
        self::assertEquals('', $this->ofsContent->signOn->status->message);
        self::assertEquals('0', $this->ofsContent->signOn->status->code);
        self::assertEquals('INFO', $this->ofsContent->signOn->status->severity);
        self::assertEquals('Success', $this->ofsContent->signOn->status->codeDesc);

        self::assertInstanceOf('DateTime', $this->ofsContent->signOn->date);
        self::assertEquals('ENG', $this->ofsContent->signOn->language);
        self::assertEquals('MYBANK', $this->ofsContent->signOn->institute->name);
        self::assertEquals('01234', $this->ofsContent->signOn->institute->id);
    }

    /**
     * @throws Exception
     */
    public function testBuildsMultipleBankAccounts(): void
    {
        $multiOfxFile = dirname(__DIR__) . '/fixtures/ofx-multiple-accounts-xml.ofx';
        $multiOfxData = simplexml_load_string(file_get_contents($multiOfxFile));
        $ofx = new Ofx($multiOfxData);

        self::assertCount(3, $ofx->bankAccounts);
        self::assertEmpty($ofx->bankAccount);
    }

    public function testICanCheckDetailsOfTransactions(): void
    {
        $transactions = $this->ofsContent->getTransactions();
        self::assertCount(3, $transactions);

        $expectedTransactions = [
            [
                'type' => 'CREDIT',
                'typeDesc' => 'Generic credit',
                'amount' => '200.00',
                'uniqueId' => '980315001',
                'name' => 'DEPOSIT',
                'memo' => 'automatic deposit',
                'sic' => '',
                'checkNumber' => ''
            ],
            [
                'type' => 'CREDIT',
                'typeDesc' => 'Generic credit',
                'amount' => '150.00',
                'uniqueId' => '980310001',
                'name' => 'TRANSFER',
                'memo' => 'Transfer from checking',
                'sic' => '',
                'checkNumber' => ''
            ],
            [
                'type' => 'CHECK',
                'typeDesc' => 'Cheque',
                'amount' => '-100.00',
                'uniqueId' => '980309001',
                'name' => 'Cheque',
                'memo' => '',
                'sic' => '',
                'checkNumber' => '1025'
            ],
        ];

        foreach ($transactions as $i => $transaction) {
            self::assertEquals($expectedTransactions[$i]['type'], $transaction->type);
            self::assertEquals($expectedTransactions[$i]['typeDesc'], $transaction->typeDesc);
            self::assertEquals($expectedTransactions[$i]['amount'], $transaction->amount);
            self::assertEquals($expectedTransactions[$i]['uniqueId'], $transaction->uniqueId);
            self::assertEquals($expectedTransactions[$i]['name'], $transaction->name);
            self::assertEquals($expectedTransactions[$i]['memo'], $transaction->memo);
            self::assertEquals($expectedTransactions[$i]['sic'], $transaction->sic);
            self::assertEquals($expectedTransactions[$i]['checkNumber'], $transaction->checkNumber);

            self::assertInstanceOf('DateTime', $transaction->date);
        }
    }

    public function testICanCheckTheBankAccountDetails(): void
    {
        $bankAccount = $this->ofsContent->bankAccount;
        self::assertEquals('23382938', $bankAccount->transactionUid);
        self::assertEquals('098-121', $bankAccount->accountNumber);
        self::assertEquals('987654321', $bankAccount->routingNumber);
        self::assertEquals('SAVINGS', $bankAccount->accountType);
        self::assertEquals('5250.00', $bankAccount->balance);
        self::assertInstanceOf('DateTime', $bankAccount->balanceDate);
    }

    public function testICanCheckTheBankAccountStatementDetails(): void
    {
        $statement = $this->ofsContent->bankAccount->statement;
        self::assertEquals('USD', $statement->currency);
        self::assertInstanceOf('DateTime', $statement->startDate);
        self::assertInstanceOf('DateTime', $statement->endDate);
    }
}
