<?php

/**
 * PagosonlineGatewayBundle for Symfony2
 *
 * This Bundle is part of Symfony2 Payment Suite
 *
 * @package PagosonlineGatewayBundle
 *
 */

namespace Scastells\PagosonlineGatewayBundle\Services\Wrapper;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Mmoreram\PaymentCoreBundle\Services\interfaces\PaymentBridgeInterface;
use Symfony\Component\Form\FormFactory;

/**
 * PagosonlineGatewayBundle manager
 */
class PagosonlineGatewayTypeWrapper
{

    /**
     * @var FormFactory
     * 
     * Form factory
     */
    protected $formFactory;


    /**
     * @var PaymentBridge
     * 
     * Payment bridge
     */
    private $paymentBridge;


    /**
     * @var string
     *
     * Encryption key
     */
    private $key;


    /**
     * @var string
     * 
     * User id
     */
    private $userId;


    /**
     * @var  boolean
     * 
     * Seller name
     */
    private $test;


    /**
     * @var string
     *
     * url gateway pagosonline
     */
    private $gateway;


    /**
     * @var string
     *
     * url pagosonline gateway response
     */
    private $response;


    /**
     * Formtype construct method
     *
     * @param FormFactory $formFactory Form factory
     * @param PaymentBridgeInterface $paymentBridge Payment bridge
     * @param $key encryption key
     * @param $userId user id
     * @param $test test environment
     * @param $gateway url gateway pagosonline
     * @param $confirmation url confirmation order status
     *
     */
    public function __construct(FormFactory $formFactory, PaymentBridgeInterface $paymentBridge, $userId, $key, $test, $gateway)
    {
        $this->formFactory = $formFactory;
        $this->paymentBridge = $paymentBridge;
        $this->userId = $userId;
        $this->key = $key;
        $this->test = $test;
        $this->gateway = $gateway;
    }


    /**
     * Builds form given success and fail urls
     *
     * @return Form
     */
    public function buildForm($responseRoute, $confirmRoute)
    {

        $extraData = $this->paymentBridge->getExtraData();
        $formBuilder = $this
            ->formFactory
            ->createNamedBuilder(null);

        //$signature = $this->key.'~'.$this->userId.'~'.$this->paymentBridge->getOrderID().'~'.$this->paymentBridge->getAmount().'~'.$this->paymentBridge->getCurrency();
        $key = $this->key;
        $userId = $this->userId;
        $orderId = $this->paymentBridge->getOrderId() . '#' . date('Ymdhis');
        $amount = $this->paymentBridge->getAmount();
        $currency = $this->paymentBridge->getCurrency();

        $signature = "$key~$userId~$orderId~$amount~$currency";
        $signatureHash = md5($signature);

        $formBuilder
            ->setAction($this->gateway)
            ->setMethod('POST')

            /**
             * Parameters injected by construct
             */
            ->add('usuarioId', 'hidden', array(
                'data'  =>  $this->userId,
            ))
            ->add('firma', 'hidden', array(
                'data'  =>  $signatureHash,
            ))
            ->add('refVenta', 'hidden', array(
                'data'  =>  $orderId,
            ))
            ->add('extra1', 'hidden', array(
                'data'  =>  'pagosonlinegateway',
            ))
            ->add('extra2', 'hidden', array(
                'data'  => $this->paymentBridge->getOrder()->getCart()->getId(),
            ))
            ->add('descripcion', 'hidden', array(
                'data'  =>  'description',
            ))
            /**
             * Payment bridge data
             */
            ->add('valor', 'hidden', array(
                'data'  =>  $this->paymentBridge->getAmount()
            ))
            ->add('moneda', 'hidden', array(
                'data'  =>  $this->paymentBridge->getCurrency(),
            ))
            ->add('lng', 'hidden', array(
                'data'  =>  $extraData['language'],
            ))
            ->add('iva', 'hidden', array(
                'data'  =>  $extraData['refund_vat'],
            ))
            ->add('baseDevolucionIva', 'hidden', array(
                'data'  =>  $extraData['refund_vat'],
            ))

            /**
             * Extra data
             */
            ->add('url_respuesta', 'hidden', array(
                'data'  =>  $responseRoute,
            ))
            ->add('url_confirmacion', 'hidden', array(
                'data'  => $confirmRoute,
            ))
            ->add('prueba', 'hidden', array(
                'data'  =>  $this->test,
            ))
            ->add('emailComprador', 'hidden', array(
                'data'  =>  $extraData['customer_email'],
            ))
            ->add('paisEnvio', 'hidden', array(
                'data'  =>  'CO',
            ))
            ->add('Submit', 'hidden', array(
                'data'  =>  'Pagar',
            ))
            ;
        return $formBuilder;
    }

}