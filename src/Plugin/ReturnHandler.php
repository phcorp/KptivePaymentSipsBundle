<?php

namespace Kptive\PaymentSipsBundle\Plugin;

use JMS\Payment\CoreBundle\Model\PaymentInstructionInterface;
use JMS\Payment\CoreBundle\PluginController\PluginControllerInterface;
use JMS\Payment\CoreBundle\PluginController\Result;

/**
 * @author Hubert Moutot <hubert.moutot@gmail.com>
 */
class ReturnHandler
{
    /**
     * @var PluginControllerInterface
     */
    protected $pluginController;

    /**
     * Constructor.
     *
     * @param PluginControllerInterface $pluginController a PluginControllerInterface instance
     */
    public function __construct(PluginControllerInterface $pluginController)
    {
        $this->pluginController = $pluginController;
    }

    /**
     * Processes the payment.
     *
     * @param PaymentInstructionInterface $instruction the PaymentInstruction to handle
     * @param array                       $data        the decrypted response data
     *
     * @return Result
     */
    public function handle(PaymentInstructionInterface $instruction, $data)
    {
        if (null === $pendingTransaction = $instruction->getPendingTransaction()) {
            $payment = $this->pluginController->createPayment($instruction->getId(), $instruction->getAmount() - $instruction->getDepositedAmount());
        } else {
            $payment = $pendingTransaction->getPayment();
        }

        foreach ($data as $key => $val) {
            $instruction->getExtendedData()->set($key, $val);
        }

        $result = $this->pluginController->approveAndDeposit($payment->getId(), $payment->getTargetAmount());

        return $result;
    }
}
