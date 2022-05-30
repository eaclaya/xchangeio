import React from "react";
import {AppLink, NavigationMenu} from '@shopify/app-bridge/actions';
import {useAppBridge} from '@shopify/app-bridge-react';
import {useLocation} from 'react-router-dom';

function AppNavigation() {
    const app = useAppBridge();

    const location = useLocation();

    const home = AppLink.create(app, {
        label: 'Home',
        destination: '/',
    });

    const example = AppLink.create(app, {
        label: 'Example',
        destination: '/example',
    });

    const sync = AppLink.create(app, {
        label: 'Sync',
        destination: '/sync',
    });

    const navigationMenu = NavigationMenu.create(app, {
        items: [home],
    });

    switch (location.pathname) {
        case "/":
            navigationMenu.set({active: home});
            break;
        case "/example":
            navigationMenu.set({active: example});
            break;
        case "/sync":
            navigationMenu.set({active: sync});
            break;
        default:
            navigationMenu.set({active: undefined});
    }

    return null
}

export default AppNavigation;
