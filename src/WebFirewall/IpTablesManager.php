<?php

namespace Barany\WebFirewall;

use Ramsey\Uuid\Uuid;

class IpTablesManager
{
    const SERVICE_TYPE_ALL = 'all';
    const SERVICE_TYPE_SSH = 'ssh';
    const SERVICE_TYPE_FTP = 'ftp';

    const RULES_CACHE_KEY = 'rules';

    /**
     * @var array
     */
    private static $SERVICE_TYPES = [
        self::SERVICE_TYPE_ALL => '',
        self::SERVICE_TYPE_SSH => '-p tcp --dport 22',
        self::SERVICE_TYPE_FTP => '-p tcp --match multiport --dports 20,21',
    ];

    /**
     * @var DataStoreInterface
     */
    private $dataStore;

    /**
     * @var array
     */
    private $rules;

    /**
     * @var string
     */
    private $distDir;

    /**
     * @var string
     */
    private $chainName;

    /**
     * @param DataStoreInterface $dataStore
     * @param array $config
     */
    public function __construct(DataStoreInterface $dataStore, array $config)
    {
        $this->dataStore = $dataStore;
        $this->rules = $this->dataStore->get(self::RULES_CACHE_KEY, []);
        $this->distDir = $config['distDir'];
        $this->chainName = $config['chainName'];
    }

    /**
     * @param string $serviceType
     * @param string $ip
     * @param string $description
     * @return string UUID of new Item
     * @throws \InvalidArgumentException
     */
    public function addRule($serviceType, $ip, $description)
    {
        if (!isset(self::$SERVICE_TYPES[$serviceType])) {
            throw new \InvalidArgumentException(sprintf('Invalid Service Type [%s]', $serviceType));
        }
        if (filter_var($ip, FILTER_VALIDATE_IP) === false) {
            throw new \InvalidArgumentException(sprintf('Invalid IP [%s]', $ip));
        }
        $uuid = Uuid::uuid4()->toString();
        $this->rules[$uuid] = [
            'host' => $ip,
            'type' => $serviceType,
            'description' => $description,
        ];
        $this->dataStore->set(self::RULES_CACHE_KEY, $this->rules);
        $this->buildRules();
        return $uuid;
    }

    /**
     * @param string $uuid
     * @return self
     */
    public function deleteRule($uuid)
    {
        unset($this->rules[$uuid]);
        $this->dataStore->set(self::RULES_CACHE_KEY, $this->rules);
        $this->buildRules();
        return $this;
    }

    /**
     * @return array
     */
    public function getAllRules()
    {
        return $this->rules;
    }

    private function buildRules()
    {
        $rulesScript = $this->distDir . '/iptables-rules';
        file_put_contents($rulesScript, $this->getContentForFile());
        chmod($rulesScript, 0755);
        file_put_contents($this->distDir . '/flag.reload', '');
    }

    /**
     * @return string
     */
    private function getContentForFile()
    {
        $content = <<<SCRIPT
#!/usr/bin/env bash

IPT="/sbin/iptables"

\$IPT -F $this->chainName


SCRIPT;
        foreach ($this->rules as $rule) {
            $content .= '# ' . $rule['description'] . "\n";
            $content .= sprintf(
                '$IPT -A %s -s %s %s -j ACCEPT' . "\n",
                $this->chainName,
                $rule['host'],
                self::$SERVICE_TYPES[$rule['type']]
            );
        }
        $content .= "\n" . '# Save the rules for server restarts' . "\n";
        $content .= '/sbin/iptables-save' . "\n";
        return $content;
    }
}
