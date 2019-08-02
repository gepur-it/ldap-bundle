# ldap-bundle
Authorisation bundle fot gepur apps

Fix security.yaml:
    
add provider to providers section  in security.yaml

    security:
        ...
        providers:
            gepur_ldap:
            id: GepurIt\LdapBundle\Contracts\ErpUserProviderInterface
            
add api key and ldap authenticators:
 
    security:
        ...
        firewalls:
            ...
            main:
                guard:
                    authenticators:
                        - GepurIt\LdapBundle\Guard\ApiKeyAuthenticator
                        - GepurIt\LdapBundle\Guard\LdapAuthenticator
                    entry_point: GepurIt\LdapBundle\Guard\ApiKeyAuthenticator
                     
add logout handler (to clear api key):

    security:
        ...
        firewalls:
            ...
            main:
                ...
                logout:
                    path:   logout
                    target: /login
                    invalidate_session: true
                    success_handler: GepurIt\LdapBundle\Logout\LogoutSuccessHandler
                    handlers: [GepurIt\LdapBundle\Logout\LogoutHandler]


full added configs:

    security:
        ...
        providers:
            gepur_ldap:
            id: GepurIt\LdapBundle\Contracts\ErpUserProviderInterface
        firewalls:
            dev:
                pattern: ^/(_(profiler|wdt)|css|images|js)/
                security: false

            main:
                pattern: ^/
                stateless: true
                anonymous: ~
                logout:
                    path:   logout
                    target: /login
                    invalidate_session: true
                    success_handler: GepurIt\LdapBundle\Logout\LogoutSuccessHandler
                    handlers: [GepurIt\LdapBundle\Logout\LogoutHandler]
                guard:
                    authenticators:
                        - GepurIt\LdapBundle\Guard\ApiKeyAuthenticator
                        - GepurIt\LdapBundle\Guard\LdapAuthenticator
                    entry_point: GepurIt\LdapBundle\Guard\ApiKeyAuthenticator
