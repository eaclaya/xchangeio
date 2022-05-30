import React from 'react';
import ReactDOM from 'react-dom';
import {Redirect} from "@shopify/app-bridge/actions";
import {authenticatedFetch} from "@shopify/app-bridge-utils"
import {ApolloClient, HttpLink, InMemoryCache} from '@apollo/client';
import {ApolloProvider} from '@apollo/client/react';
import {AppProvider} from "@shopify/polaris";
import translations from "@shopify/polaris/locales/en.json";
import '@shopify/polaris/dist/styles.css';
import PageLayout from "./components/PageLayout";
import ProductsPage from "./components/ProductsPage";
import {Provider, useAppBridge} from '@shopify/app-bridge-react';
import {BrowserRouter, Route, Switch} from "react-router-dom";
import ClientRouter from "./components/ClientRouter";
import AppNavigation from "./components/AppNavigation";
import {Button} from '@shopify/polaris';
import {Link} from '@shopify/polaris';
import {Card} from '@shopify/polaris';

function userLoggedInFetch(app) {
    const fetchFunction = authenticatedFetch(app);

    return async (uri, options) => {
        const response = await fetchFunction(uri, options);

        if (response.headers.get("X-Shopify-API-Request-Failure-Reauthorize") === "1") {
            const authUrlHeader = response.headers.get("X-Shopify-API-Request-Failure-Reauthorize-Url");

            const redirect = Redirect.create(app);
            redirect.dispatch(Redirect.Action.APP, authUrlHeader);
            return null;
        }

        return response;
    };
}

function AppBridgeApolloProvider({children}) {
    const app = useAppBridge();
    const client = new ApolloClient({
        link: new HttpLink({
            credentials: 'same-origin',
            fetch: userLoggedInFetch(app),
            uri: '/graphql'
        }),
        cache: new InMemoryCache()
    });

    return (
        <ApolloProvider client={client}>
            {children}
        </ApolloProvider>
    );
}


function HomePage({code}) {
    return <Card title="Exchange app links" sectioned><p><Button primary fullWidth external={true} url="https://exchanges.mottandbow.com">Front page</Button></p><p><Button primary fullWidth external={true} url="https://dashboard.mottandbow.com">Back page</Button></p></Card>
}
function App({shop, code, host, apiKey}) {
    const config = {apiKey: apiKey, shopOrigin: shop, host: host, forceRedirect: true};
    const frontendUrl = `https://exchanges.mottandbow.com/${code}`;
    const backendUrl = `https://dashboard.mottandbow.com`;
    return (
        <BrowserRouter>
            <Provider config={config}>
                <ClientRouter/>
                <AppProvider i18n={translations}>
                    <AppBridgeApolloProvider>
                        <AppNavigation/>
                        <PageLayout>
                            <Card title="Exchange app links" sectioned>
                                <Button primary fullWidth external={true} url={frontendUrl}>Front page</Button>
                                <Button primary fullWidth external={true} url={backendUrl}>Back page</Button>
                            </Card>
                        </PageLayout>
                    </AppBridgeApolloProvider>
                </AppProvider>
            </Provider>
        </BrowserRouter>
    );
}

export default App;

let appElement = document.getElementById('app');
if (appElement) {
    ReactDOM.render(<App {...(appElement.dataset)}/>, appElement);
}
