# ldap-bundle
Authorisation bundle fot gepur apps

Fix security.yaml:
    
    security:
        always_authenticate_before_granting:  true
        access_decision_manager:
            strategy:             affirmative # One of affirmative, consensus, unanimous
            allow_if_all_abstain:  false
            allow_if_equal_granted_denied:  true
        providers:
            gepur_ldap:
                id: ldap.user_provider
            api_key_user_provider:
                id: ldap.api_key.user_provider
        firewalls:
            main:
                pattern: ^/
                stateless: true
                anonymous: ~
                provider: 'gepur_ldap'
                simple_preauth:
                    authenticator: ldap.api_key.authenicator
                    provider: api_key_user_provider
                logout:
                    path:   logout
                    target: /login
                    invalidate_session: true
                    success_handler: ldap.logout.success_handler
                    handlers: [ldap.logout.log_handler, ldap.logout.api_key_handler]
                form_login_ldap:
                    check_path: login
                    login_path: login
                    use_forward: false
                    default_target_path: homepage
                    provider: 'gepur_ldap'
                    service: Symfony\Component\Ldap\Ldap
                    dn_string: '%ldap_dn%'
                    post_only:      true
                    remember_me:    false
                    query_string: '(samaccountname={username})'
                    success_handler: ldap.login.success_handler
                    failure_handler: ldap.login.failure_handler
                    require_previous_session: false
