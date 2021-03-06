<?php

use outlet\Proxy;
use outlet\OutletException;
use outlet\Config;
use outlet\ConfigException;

class Unit_ConfigTest extends OutletTestCase {
	public function testRequireConnectionConfig() {
		try {
			$config = new Config(array());
		} catch (ConfigException $ex) { $this->assertTrue(true); return; }
		$this->fail("should've raised an exception");
	}

	public function testRequireConnectionDsnOrPdo() {
		try {
			$config = new Config(array('connection' => 1));
		} catch (ConfigException $ex) { $this->assertTrue(true); return; }
		$this->fail("should've raised an exception");
	}

	public function testRequireDialect() {
		// TODO: this test looks ugly
		try {
			$config = new Config(array('connection' => array('dsn' => $this->getSQLiteInMemoryDSN())));
		} catch (OutletException $ex) {
			$this->assertTrue(true);
			try {
				$config = new Config(array('connection' => array('pdo' => 1)));
			} catch (ConfigException $ex) { $this->assertTrue(true); return; }
		}
		$this->fail("should've raised an exception");
	}

	public function testRequireClassesMapping() {
		try {
			$config = new Config(array('connection' => array('dsn' => $this->getSQLiteInMemoryDSN(), 'dialect' => 'mysql')));
		} catch (ConfigException $ex) { $this->assertTrue(true); return;}
		$this->fail("should've raised an exception");
	}

	public function testCanGetConnection() {
		$config = new Config(array('connection' => array('dsn' => $this->getSQLiteInMemoryDSN(), 'dialect' => 'sqlite'), 'classes' => array()));
		$this->assertThat($config->getConnection(), $this->isInstanceOf('outlet\Connection'));

		$config = new Config(array('connection' => array('pdo' => $this->getSQLiteInMemoryPDOConnection(), 'dialect' => 'sqlite'), 'classes' => array()));
		$this->assertThat($config->getConnection(), $this->isInstanceOf('outlet\Connection'));
	}

	public function testRaisesExceptionIfEntityNotFound() {
		$config = new Config(array('connection' => array('pdo' => $this->getSQLiteInMemoryPDOConnection(), 'dialect' => 'sqlite'), 'classes' => array('Testing' => array('table' => 'testing', 'props' => array('id' => array('id', 'int', array('pk' => true)))))));
		try {
			$this->assertThat($config->getEntity('Testing2'), $this->isInstanceOf('outlet\EntityConfig'));
		} catch (OutletException $ex) { $this->assertTrue(true); return; }
		$this->fail("should've raised an exception");
	}

	public function testAllowToSuppresExceptionIfEntityNotFound() {
		$config = new Config(array('connection' => array('pdo' => $this->getSQLiteInMemoryPDOConnection(), 'dialect' => 'sqlite'), 'classes' => array('Testing' => array('table' => 'testing', 'props' => array('id' => array('id', 'int', array('pk' => true)))))));
		$this->assertNull($config->getEntity('Testing2', false));
	}

	public function testGettersAndSettersAreDisabledByDefault() {
		$config = $this->_createConfig();
		$this->assertFalse($config->useGettersAndSetters());
		$this->assertFalse($config->getEntity('ConfigEntity')->useGettersAndSetters());
	}

	public function testSetGettersAndSettersUsageGlobal() {
		$config = $this->_createConfig(true);
		$this->assertTrue($config->useGettersAndSetters());
		$this->assertTrue($config->getEntity('ConfigEntity')->useGettersAndSetters());
	}

	public function testSetGettersAndSettersUsagePerEntity() {
		$config = $this->_createConfig(true, false);
		$this->assertFalse($config->getEntity('ConfigEntity')->useGettersAndSetters());

		$config = $this->_createConfig(false, true);
		$this->assertTrue($config->getEntity('ConfigEntity')->useGettersAndSetters());
	}

	public function testCanGetEntityConfig() {
		$config = new Config(array('connection' => array('pdo' => $this->getSQLiteInMemoryPDOConnection(), 'dialect' => 'sqlite'), 'classes' => array('Testing' => array('table' => 'testing', 'props' => array('id' => array('id', 'int', array('pk' => true)))))));
		$this->assertThat($config->getEntity('Testing'), $this->isInstanceOf('outlet\EntityConfig'));
	}

	public function testCanGetSubclassEntityConfig() {
		$config = new Config(array('connection' => array('pdo' => $this->getSQLiteInMemoryPDOConnection(), 'dialect' => 'sqlite'), 'classes' => array('Testing' => array('table' => 'testing', 'subclasses'=>array('Subtesting'=>array('discriminator-value'=>'subtesting','props'=>array())),'discriminator'=>'type', 'discriminator-value'=>'superclass', 'props' => array('id' => array('id', 'int', array('pk' => true)))))));
		$this->assertThat($config->getEntity('Subtesting'), $this->isInstanceOf('outlet\EntityConfig'));
	}
	
	public function testGettingEntityConfigByObject() {
		$config = $this->_createConfig();
		$this->assertThat($config->getEntity(new ConfigEntity()), $this->isInstanceOf('outlet\EntityConfig'));
	}

	public function testGettingEntityConfigByProxy() {
		$config = $this->_createConfig();
		$this->assertThat($config->getEntity(new ConfigEntity_OutletProxy()), $this->isInstanceOf('outlet\EntityConfig'));
	}

	public function testGettingEntityConfigByAlias() {
		$config = $this->_createConfig(null, null, null, null, 'EntityAlias');
		$this->assertThat($config->getEntity('EntityAlias'), $this->isInstanceOf('outlet\EntityConfig'));
	}

	public function testProxyAutoloadingDefaultsToDisabled() {
		$config = $this->_createConfig();

		$this->assertFalse($config->autoloadProxies);
		$this->assertFalse($config->proxiesCache);
	}

	public function testProxyAutoloadingEnabled() {
		$config = $this->_createConfig(null, null, true);

		$this->assertTrue($config->autoloadProxies);
		$this->assertFalse($config->proxiesCache);
	}

	public function testProxyAutoloadingAndCachingEnabled() {
		$config = $this->_createConfig(null, null, true, 'directory');

		$this->assertEquals('directory', $config->proxiesCache);
	}

	public function testDetectsEntityAliasCollision() {
		try {
			$config = $this->_createConfig(null, null, null, null, null, 'app\model\ConfigEntity');
			$config->getEntities();
		} catch (ConfigException $ex) { $this->assertTrue(true); return; }
		$this->fail("should've raised an exception");
	}

	protected function _createConfigArray($globalGettersAndSetters = null,
					$entityGettersAndSetters = null,
					$proxyAutoloading = null,
					$proxiesCache = null,
					$alias = null,
					$duplicatedEntityClass = null) {
		

		return $config;
	}

	protected function _createConfig($globalGettersAndSetters = null,
					$entityGettersAndSetters = null,
					$proxyAutoloading = null,
					$proxiesCache = null,
					$alias = null,
					$duplicatedEntityClass = null) {
		$config = array(
			'connection' => array(
				'pdo' => $this->getSQLiteInMemoryPDOConnection(),
				'dialect' => 'sqlite'
			),
			'classes' => array(
				'ConfigEntity' => array(
					'table' => 'testing',
					'alias' => $alias,
					'props' => array('id' => array('id', 'int', array('pk' => true)))
				)
			)
		);

		if ($globalGettersAndSetters !== null)
			$config['useGettersAndSetters'] = $globalGettersAndSetters;

		if ($entityGettersAndSetters !== null)
			$config['classes']['ConfigEntity']['useGettersAndSetters'] = $entityGettersAndSetters;
		if ($duplicatedEntityClass !== null)
			$config['classes'][$duplicatedEntityClass] = $config['classes']['ConfigEntity'];

		if ($proxyAutoloading !== null) {
			$config['proxies'] = array();
			$config['proxies']['autoload'] = true;
			$config['proxies']['cache'] = $proxiesCache;
		}
		
		return new Config($config);
	}
}

class ConfigEntity {
	public $id;
}

class ConfigEntity_OutletProxy extends ConfigEntity implements Proxy {}