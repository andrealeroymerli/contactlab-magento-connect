<?php

class Contactlab_Subscribers_Test_Model_Uk extends EcomDev_PHPUnit_Test_Case
{
    const TEST_EMAIL = 'name.surname@example.com';
    /** @var Contactlab_Subscribers_Model_Uk */
    private $model;
    /** @var Contactlab_Subscribers_Helper_Uk */
    private $helper;


    /**
     * Setup.
     */
    protected function setUp() {
        $sessionMock = $this->getModelMockBuilder('adminhtml/session')
            ->disableOriginalConstructor() // This one removes session_start and other methods usage
            ->setMethods(null) // Enables original methods usage, because by default it overrides all methods
            ->getMock();
        $this->replaceByMock('singleton', 'adminhtml/session', $sessionMock);

        $this->model = Mage::getModel('contactlab_subscribers/uk');
        $this->helper = Mage::helper('contactlab_subscribers/uk');
        $this->model->truncate();
        foreach (Mage::getModel('newsletter/subscriber')->getCollection() as $item) {
            $item->delete();
        }
        foreach (Mage::getModel('customer/customer')->getCollection() as $item) {
            $item->delete();
        }
    }


    /**
     * @test
     */
    public function firstTest() {
        $this->assertEquals($this->getUkCount(), 0);
    }

    /**
     * Test subscriber
     */
    public function testSubscriber() {
        $this->assertEquals($this->getUkCount(), 0);
        $subscriber = $this->createSubscriber();
        $subscriberId = $subscriber->getSubscriberId();

        $this->helper->update(null, $subscriberId);
        $this->assertEquals($this->getUkCount(), 1);
        $uk = $this->helper->searchBySubscriberId($subscriberId);
        $this->assertEquals($uk->getSubscriberId(), $subscriberId);
        $this->assertNull($uk->getCustomerId());
        $this->model->purge(true);
        $this->assertEquals($this->getUkCount(), 1);
        $this->model->truncate();
        $this->assertEquals($this->getUkCount(), 0);
    }

    /**
     * Test customer
     */
    public function testCustomer() {
        $this->assertEquals($this->getUkCount(), 0);
        $customer = $this->createCustomer();
        $customerId = $customer->getEntityId();

        $this->helper->update($customerId, null);
        $this->assertEquals($this->getUkCount(), 1);
        $uk = $this->helper->searchByCustomerId($customerId);
        $this->assertEquals($uk->getCustomerId(), $customerId);
        $this->assertNull($uk->getSubscriberId());
        $this->model->purge(true);
        $this->assertEquals($this->getUkCount(), 1);
        $this->model->truncate();
        $this->assertEquals($this->getUkCount(), 0);
    }

    /**
     * Test customer
     */
    public function testSubscribeACustomer() {
        $this->assertEquals($this->getUkCount(), 0);
        $customer = $this->createCustomer();
        $customerId = $customer->getEntityId();

        $this->helper->update($customerId, null);
        $this->assertEquals($this->getUkCount(), 1);
        $uk = $this->helper->searchByCustomerId($customerId);
        $this->assertEquals($uk->getCustomerId(), $customerId);
        $this->assertNull($uk->getSubscriberId());

        $subscriber = $this->createSubscriber();
        $subscriber->subscribeCustomer($customer);
        $subscriber->save();
        $subscriberId = $subscriber->getSubscriberId();
        $this->helper->update($customerId, null);


        $uk = $this->helper->searchByCustomerId($customerId);
        $this->assertNotNull($customerId);
        $this->assertNotNull($subscriberId);
        $this->assertNotNull($uk->getCustomerId());
        $this->assertNotNull($uk->getSubscriberId());
        $this->assertEquals($uk->getCustomerId(), $customerId);
        $this->assertEquals($uk->getSubscriberId(), $subscriberId);


        $this->model->purge(true);
        $this->assertEquals($this->getUkCount(), 1);
        $this->model->truncate();
        $this->assertEquals($this->getUkCount(), 0);
    }

    /**
     * Test customer
     */
    public function testCustomerASubscriber() {
        $this->assertEquals($this->getUkCount(), 0);
        $subscriber = $this->createSubscriber();
        $subscriber->save();
        $subscriberId = $subscriber->getSubscriberId();

        $customer = $this->createCustomer();
        $customerId = $customer->getEntityId();
        $subscriber->subscribeCustomer($customer)->save();
        $this->helper->update($customerId, $subscriberId);

        $uk = $this->helper->searchBySubscriberId($subscriberId);
        $this->assertNotNull($customerId);
        $this->assertNotNull($subscriberId);
        $this->assertNotNull($uk->getCustomerId());
        $this->assertNotNull($uk->getSubscriberId());
        $this->assertEquals($uk->getCustomerId(), $customerId);
        $this->assertEquals($uk->getSubscriberId(), $subscriberId);


        $this->model->purge(true);
        $this->assertEquals($this->getUkCount(), 1);
        $this->model->truncate();
        $this->assertEquals($this->getUkCount(), 0);
    }

        /**
     * Get Count.
     * @return int
     */
    private function getUkCount() {
        $collection = Mage::getModel('contactlab_subscribers/uk')->getCollection();
        return $collection->getSize();
    }

    private function createCustomer() {
        $customer = Mage::getModel('customer/customer');
        $customer->setData('email', self::TEST_EMAIL);
        return $customer->save();
    }

    private function createSubscriber() {
        $subscriber = Mage::getModel('newsletter/subscriber');
        $subscriber->setSubscriberEmail(self::TEST_EMAIL);
        return $subscriber->save();
    }
}