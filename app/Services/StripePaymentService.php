<?php
// app/Services/StripePaymentService.php

namespace App\Services;

use App\Models\Appointment;
use Stripe\Stripe;
use Stripe\PaymentIntent;
use Stripe\Refund;
use Stripe\Customer;
use Exception;

class StripePaymentService
{
    public function __construct()
    {
        Stripe::setApiKey(config('services.stripe.secret'));
    }

    /**
     * Create payment intent for appointment
     */
    public function createPaymentIntent(Appointment $appointment)
    {
        try {
            $paymentIntent = PaymentIntent::create([
                'amount' => $this->convertToCents($appointment->final_amount),
                'currency' => config('services.stripe.currency', 'usd'),
                'metadata' => [
                    'appointment_id' => $appointment->id,
                    'appointment_number' => $appointment->appointment_number,
                    'client_id' => $appointment->client_id,
                ],
                'description' => "Appointment: {$appointment->appointment_number}",
            ]);

            return [
                'success' => true,
                'client_secret' => $paymentIntent->client_secret,
                'payment_intent_id' => $paymentIntent->id,
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Process payment with payment method
     */
    public function processPayment(Appointment $appointment, $paymentMethodId)
    {
        try {
            $paymentIntent = PaymentIntent::create([
                'amount' => $this->convertToCents($appointment->final_amount),
                'currency' => config('services.stripe.currency', 'usd'),
                'payment_method' => $paymentMethodId,
                'confirmation_method' => 'manual',
                'confirm' => true,
                'return_url' => route('appointments.payment.callback', $appointment->id),
                'metadata' => [
                    'appointment_id' => $appointment->id,
                    'appointment_number' => $appointment->appointment_number,
                ],
            ]);

            if ($paymentIntent->status === 'succeeded') {
                return [
                    'success' => true,
                    'payment_intent_id' => $paymentIntent->id,
                    'charge_id' => $paymentIntent->latest_charge,
                ];
            } elseif ($paymentIntent->status === 'requires_action') {
                return [
                    'success' => false,
                    'requires_action' => true,
                    'client_secret' => $paymentIntent->client_secret,
                ];
            } else {
                return [
                    'success' => false,
                    'error' => 'Payment failed. Please try again.',
                ];
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Confirm payment intent
     */
    public function confirmPayment($paymentIntentId)
    {
        try {
            $paymentIntent = PaymentIntent::retrieve($paymentIntentId);
            
            if ($paymentIntent->status === 'requires_confirmation') {
                $paymentIntent->confirm();
            }

            return [
                'success' => $paymentIntent->status === 'succeeded',
                'status' => $paymentIntent->status,
                'charge_id' => $paymentIntent->latest_charge,
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Refund payment
     */
    public function refundPayment(Appointment $appointment, $amount = null)
    {
        try {
            if (!$appointment->stripe_payment_intent_id) {
                throw new Exception('No payment to refund.');
            }

            $refundData = [
                'payment_intent' => $appointment->stripe_payment_intent_id,
            ];

            if ($amount) {
                $refundData['amount'] = $this->convertToCents($amount);
            }

            $refund = Refund::create($refundData);

            if ($refund->status === 'succeeded') {
                $appointment->markRefunded();
            }

            return [
                'success' => $refund->status === 'succeeded',
                'refund_id' => $refund->id,
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Create or get Stripe customer
     */
    public function getOrCreateCustomer($user)
    {
        try {
            // Check if user already has a Stripe customer ID stored
            // You might want to add a stripe_customer_id column to your users/clients table
            
            $customer = Customer::create([
                'email' => $user->email,
                'name' => $user->name,
                'metadata' => [
                    'user_id' => $user->id,
                ],
            ]);

            return [
                'success' => true,
                'customer_id' => $customer->id,
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Convert amount to cents
     */
    protected function convertToCents($amount)
    {
        return (int) ($amount * 100);
    }
}