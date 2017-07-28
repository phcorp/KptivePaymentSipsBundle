<?php

namespace Kptive\PaymentSipsBundle\Tests\Plugin;

use JMS\Payment\CoreBundle\Entity\FinancialTransaction;
use JMS\Payment\CoreBundle\Entity\ExtendedData;
use JMS\Payment\CoreBundle\Model\FinancialTransactionInterface;
use JMS\Payment\CoreBundle\Plugin\PluginInterface;
use Kptive\PaymentSipsBundle\Plugin\SipsPlugin;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use JMS\Payment\CoreBundle\Plugin\Exception\FinancialException;

/**
 * @author Hubert Moutot <hubert.moutot@gmail.com>
 */
class SipsPluginTest extends TestCase
{
    /** @var SipsPlugin */
    private $sipsPlugin;

    public function setUp()
    {
        $logger = $this->getMockBuilder(LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->sipsPlugin = new SipsPlugin($logger);
    }

    public function testProcesses()
    {
        $this->assertTrue($this->sipsPlugin->processes('sips'));
        $this->assertFalse($this->sipsPlugin->processes('paypal_express_checkout'));
    }

    public function testApproveAndDepositFailed()
    {
        $transaction = new FinancialTransaction();

        $response = $this->getFailedResponse();
        $transaction->setExtendedData(new ExtendedData());
        foreach ($response as $key => $val) {
            $transaction->getExtendedData()->set($key, $val);
        }

        $this->expectException(FinancialException::class);
        $this->expectExceptionMessage('Payment failed with error code -1. Error: Payment failed');

        $this->sipsPlugin->approveAndDeposit($transaction, false);

        $this->assertEquals(FinancialTransactionInterface::STATE_FAILED, $transaction->getState());
        $this->assertEquals(SipsPlugin::RESPONSE_CODE_FAILED, $transaction->getResponseCode());
        $this->assertEquals('Payment error', $transaction->getReasonCode());
    }

    public function testApproveAndDepositSuccess()
    {
        $transaction = new FinancialTransaction();

        $response = $this->getSuccessfulResponse();
        $transaction->setExtendedData(new ExtendedData());
        foreach ($response as $key => $val) {
            $transaction->getExtendedData()->set($key, $val);
        }

        $this->sipsPlugin->approveAndDeposit($transaction, false);

        $this->assertEquals(PluginInterface::RESPONSE_CODE_SUCCESS, $transaction->getResponseCode());
        $this->assertEquals(PluginInterface::REASON_CODE_SUCCESS, $transaction->getReasonCode());

        $this->assertEquals(100.10, $transaction->getProcessedAmount());
        $this->assertEquals(2, $transaction->getReferenceNumber());
    }

    public function testApproveAndDepositCanceled()
    {
        $transaction = new FinancialTransaction();

        $response = $this->getCanceledResponse();
        $transaction->setExtendedData(new ExtendedData());
        foreach ($response as $key => $val) {
            $transaction->getExtendedData()->set($key, $val);
        }

        $this->sipsPlugin->approveAndDeposit($transaction, false);

        $this->assertEquals(SipsPlugin::RESPONSE_CODE_CANCELED, $transaction->getResponseCode());
        $this->assertEquals('Payment canceled', $transaction->getReasonCode());
    }

    public function getFailedResponse()
    {
        return array(
            'code' => -1,
            'error' => 'Payment failed',
        );
    }

    public function getSuccessfulResponse()
    {
        return array(
            'code' => 0,
            'response_code' => 0,
            'error' => '',
            'order_id' => 2,
            'amount' => 10010,
            'transaction_id' => 1,
        );
    }

    public function getCanceledResponse()
    {
        return array(
            'code' => 0,
            'response_code' => 17,
            'error' => '',
            'order_id' => 2,
            'amount' => 10010,
            'transaction_id' => 1,
        );
    }
}
