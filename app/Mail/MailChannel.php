<?php
namespace App\Mail;

trait MailChannel
{

    protected $mail_channels = [

        //自配邮局 lighthouse
        'lighthouse'	=> [
            'driver'	=> 'smtp',
            'host' => "smtp.lighthousemy.com", // 根据你的邮件服务提供商来填
            'port' => "465", // 同上
            'encryption' => "ssl", // 同上 一般是tls或ssl
            'username' => 'dmarc@lighthousemy.com',
            'password' => 'ad534e4efad592b!',
            'stream'	=> [
                'ssl'	=> [//设置ssl证书协议认证方式  false表示忽略
                    'verify_peer'		=> false,
                    'verify_peer_name'	=> false
                ]
            ],
            'from' => [
                'address' => 'dmarc@lighthousemy.com',
                'name' => 'dmarc',
            ]
        ],

        //自配邮局 crmcloud
        'crmcloud'	=> [
            'driver'	=> 'smtp',
            'host' => "smtp.crmcloudtech.com", // 根据你的邮件服务提供商来填
            'port' => "465", // 同上
            'encryption' => "ssl", // 同上 一般是tls或ssl
            'username' => 'service@crmcloudtech.com',
            'password' => '5e6dc09e0cdfbc8!',
            'stream'	=> [
                'ssl'	=> [//设置ssl证书协议认证方式  false表示忽略
                    'verify_peer'		=> false,
                    'verify_peer_name'	=> false
                ]
            ],
            'from' => [
                'address' => 'service@crmcloudtech.com',
                'name' => 'service',
            ]
        ],

        'fxpointcard'	=> [
            'driver'	=> 'smtp',
            'host' => "smtp.mxhichina.com", // 根据你的邮件服务提供商来填
            'port' => "465", // 同上
            'encryption' => "ssl", // 同上 一般是tls或ssl
            'username' => 'service@fxpointcard.com',
            'password' => 'Y17182112867.',
            'from' => [
                'address' => 'service@fxpointcard.com',
                'name' => 'service',
            ]
        ],

        'fxrefillcard'	=> [
            'driver'	=> 'smtp',
            'host' => "smtp.mxhichina.com", // 根据你的邮件服务提供商来填
            'port' => "465", // 同上
            'encryption' => "ssl", // 同上 一般是tls或ssl
            'username' => 'service@fxrefillcard.com',
            'password' => 'Y17182111628.',
            'from' => [
                'address' => 'service@fxrefillcard.com',
                'name' => 'service',
            ]
        ],

        'forexacard'	=> [
            'driver'	=> 'smtp',
            'host' => "smtp.mxhichina.com", // 根据你的邮件服务提供商来填
            'port' => "465", // 同上
            'encryption' => "ssl", // 同上 一般是tls或ssl
            'username' => 'service@forexacard.com',
            'password' => 'Y17182110181.',
            'from' => [
                'address' => 'service@forexacard.com',
                'name' => 'service',
            ]
        ],

        'forexbcard'	=> [
            'driver'	=> 'smtp',
            'host' => "smtp.mxhichina.com", // 根据你的邮件服务提供商来填
            'port' => "465", // 同上
            'encryption' => "ssl", // 同上 一般是tls或ssl
            'username' => 'service@forexbcard.com',
            'password' => 'Y17182111623.',
            'from' => [
                'address' => 'service@forexbcard.com',
                'name' => 'service',
            ]
        ],

        'fxcarda'	=> [
            'driver'	=> 'smtp',
            'host' => "smtp.mxhichina.com", // 根据你的邮件服务提供商来填
            'port' => "465", // 同上
            'encryption' => "ssl", // 同上 一般是tls或ssl
            'username' => 'service@fxcarda.com',
            'password' => 'Y17182112868.',
            'from' => [
                'address' => 'service@fxcarda.com',
                'name' => 'service',
            ]
        ],

        //默认邮局  阿里云企业邮箱
        'default'	=> [
            'driver'	=> 'smtp',
            'host' => "smtp.mxhichina.com", // 根据你的邮件服务提供商来填
            'port' => "465", // 同上
            'encryption' => "ssl", // 同上 一般是tls或ssl
            'username' => 'service@benefitpick.com',
            'password' => '8577848334af684!',
            'from' => [
                'address' => 'service@benefitpick.com',
                'name' => 'service',
            ]
        ],

        'info_fxpointcard'	=> [
            'driver'	=> 'smtp',
            'host' => "smtp.mxhichina.com", // 根据你的邮件服务提供商来填
            'port' => "465", // 同上
            'encryption' => "ssl", // 同上 一般是tls或ssl
            'username' => 'info@fxpointcard.com',
            'password' => 'Y17182112867.',
            'from' => [
                'address' => 'info@fxpointcard.com',
                'name' => 'info',
            ]
        ],

        'info_fxrefillcard'	=> [
            'driver'	=> 'smtp',
            'host' => "smtp.mxhichina.com", // 根据你的邮件服务提供商来填
            'port' => "465", // 同上
            'encryption' => "ssl", // 同上 一般是tls或ssl
            'username' => 'info@fxrefillcard.com',
            'password' => 'Y17182111628.',
            'from' => [
                'address' => 'info@fxrefillcard.com',
                'name' => 'info',
            ]
        ],

        'info_forexacard'	=> [
            'driver'	=> 'smtp',
            'host' => "smtp.mxhichina.com", // 根据你的邮件服务提供商来填
            'port' => "465", // 同上
            'encryption' => "ssl", // 同上 一般是tls或ssl
            'username' => 'info@forexacard.com',
            'password' => 'Y17182110181.',
            'from' => [
                'address' => 'info@forexacard.com',
                'name' => 'info',
            ]
        ],

        'info_forexbcard'	=> [
            'driver'	=> 'smtp',
            'host' => "smtp.mxhichina.com", // 根据你的邮件服务提供商来填
            'port' => "465", // 同上
            'encryption' => "ssl", // 同上 一般是tls或ssl
            'username' => 'info@forexbcard.com',
            'password' => 'Y17182111623.',
            'from' => [
                'address' => 'info@forexbcard.com',
                'name' => 'info',
            ]
        ],

        'info_fxcarda'	=> [
            'driver'	=> 'smtp',
            'host' => "smtp.mxhichina.com", // 根据你的邮件服务提供商来填
            'port' => "465", // 同上
            'encryption' => "ssl", // 同上 一般是tls或ssl
            'username' => 'info@fxcarda.com',
            'password' => 'Y17182112868.',
            'from' => [
                'address' => 'info@fxcarda.com',
                'name' => 'info',
            ]
        ],

        'system_fxpointcard'	=> [
            'driver'	=> 'smtp',
            'host' => "smtp.mxhichina.com", // 根据你的邮件服务提供商来填
            'port' => "465", // 同上
            'encryption' => "ssl", // 同上 一般是tls或ssl
            'username' => 'system@fxpointcard.com',
            'password' => 'Y17182112867.',
            'from' => [
                'address' => 'system@fxpointcard.com',
                'name' => 'system',
            ]
        ],

        'system_fxrefillcard'	=> [
            'driver'	=> 'smtp',
            'host' => "smtp.mxhichina.com", // 根据你的邮件服务提供商来填
            'port' => "465", // 同上
            'encryption' => "ssl", // 同上 一般是tls或ssl
            'username' => 'system@fxrefillcard.com',
            'password' => 'Y17182111628.',
            'from' => [
                'address' => 'system@fxrefillcard.com',
                'name' => 'system',
            ]
        ],

        'system_forexacard'	=> [
            'driver'	=> 'smtp',
            'host' => "smtp.mxhichina.com", // 根据你的邮件服务提供商来填
            'port' => "465", // 同上
            'encryption' => "ssl", // 同上 一般是tls或ssl
            'username' => 'system@forexacard.com',
            'password' => 'Y17182110181.',
            'from' => [
                'address' => 'system@forexacard.com',
                'name' => 'system',
            ]
        ],

        'system_forexbcard'	=> [
            'driver'	=> 'smtp',
            'host' => "smtp.mxhichina.com", // 根据你的邮件服务提供商来填
            'port' => "465", // 同上
            'encryption' => "ssl", // 同上 一般是tls或ssl
            'username' => 'system@forexbcard.com',
            'password' => 'Y17182111623.',
            'from' => [
                'address' => 'system@forexbcard.com',
                'name' => 'system',
            ]
        ],

        'system_fxcarda'	=> [
            'driver'	=> 'smtp',
            'host' => "smtp.mxhichina.com", // 根据你的邮件服务提供商来填
            'port' => "465", // 同上
            'encryption' => "ssl", // 同上 一般是tls或ssl
            'username' => 'system@fxcarda.com',
            'password' => 'Y17182112868.',
            'from' => [
                'address' => 'system@fxcarda.com',
                'name' => 'system',
            ]
        ],

    ];

}
