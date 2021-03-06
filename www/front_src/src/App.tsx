/* eslint-disable jsx-a11y/no-static-element-interactions */
/* eslint-disable jsx-a11y/click-events-have-key-events */
/* eslint-disable react/destructuring-assignment */
/* eslint-disable react/sort-comp */
/* eslint-disable import/no-extraneous-dependencies */
/* eslint-disable react/prop-types */
/* eslint-disable react/jsx-filename-extension */

import React, { Component, ReactNode } from 'react';
import { connect } from 'react-redux';
import { ConnectedRouter } from 'connected-react-router';
import Fullscreen from 'react-fullscreen-crossbrowser';
import queryString from 'query-string';
import { StylesProvider } from '@material-ui/styles';
import Header from './components/header';
import { history } from './store';

import NavigationComponent from './components/navigation';
import Tooltip from './components/tooltip';
import Footer from './components/footer';
import MainRouter from './components/mainRouter';
import axios from './axios';

import { fetchExternalComponents } from './redux/actions/externalComponentsActions';

import styles from './App.scss';
import footerStyles from './components/footer/footer.scss';
import contentStyles from './styles/partials/_content.scss';

// Extends Window interface
declare global {
  interface Window {
    fullscreenSearch: string;
    fullscreenHash: string;
  }
}

interface Props {
  fetchExternalComponents: () => void;
}

interface State {
  isFullscreenEnabled: boolean;
}

class App extends Component<Props, State> {
  public state = {
    isFullscreenEnabled: false,
  };

  private keepAliveTimeout: NodeJS.Timeout | null = null;

  // check in arguments if min=1
  private getMinArgument = (): boolean => {
    const { search } = history.location;
    const parsedArguments = queryString.parse(search);

    return parsedArguments.min === '1';
  };

  // enable fullscreen
  private goFull = (): void => {
    // set fullscreen url parameters
    // this will be used to init iframe src
    window.fullscreenSearch = window.location.search;
    window.fullscreenHash = window.location.hash;

    // enable fullscreen after 200ms
    setTimeout(() => {
      this.setState({ isFullscreenEnabled: true });
    }, 200);
  };

  // disable fullscreen
  private removeFullscreenParams = (): void => {
    if (history.location.pathname === '/main.php') {
      history.push({
        pathname: '/main.php',
        search: window.fullscreenSearch,
        hash: window.fullscreenHash,
      });
    }

    // remove fullscreen parameters to keep normal routing
    delete window.fullscreenSearch;
    delete window.fullscreenHash;
  };

  // keep alive (redirect to login page if session is expired)
  private keepAlive = (): void => {
    this.keepAliveTimeout = setTimeout(() => {
      axios('internal.php?object=centreon_keepalive&action=keepAlive')
        .get()
        .then(() => this.keepAlive())
        .catch((error) => {
          if (error.response && error.response.status === 401) {
            // redirect to login page
            window.location.href = 'index.php?disconnect=1';
          } else {
            // keepalive must be done cause it may failed due to temporary unavailability
            this.keepAlive();
          }
        });
    }, 15000);
  };

  public componentDidMount(): void {
    // 2 - fetch external components (pages, hooks...)
    this.props.fetchExternalComponents();

    this.keepAlive();
  }

  public render(): ReactNode {
    const min = this.getMinArgument();

    return (
      <StylesProvider injectFirst>
        <ConnectedRouter history={history}>
          <div className={styles.wrapper}>
            {!min && <NavigationComponent />}
            <Tooltip />
            <div id="content" className={contentStyles.content}>
              {!min && <Header />}
              <div
                id="fullscreen-wrapper"
                className={contentStyles['fullscreen-wrapper']}
              >
                <Fullscreen
                  enabled={this.state.isFullscreenEnabled}
                  onClose={this.removeFullscreenParams}
                  onChange={(isFullscreenEnabled): void => {
                    this.setState({ isFullscreenEnabled });
                  }}
                >
                  <div className={styles['main-content']}>
                    <MainRouter />
                  </div>
                </Fullscreen>
              </div>
              {!min && <Footer />}
            </div>
            <span
              className={footerStyles['full-screen']}
              onClick={this.goFull}
            />
          </div>
        </ConnectedRouter>
      </StylesProvider>
    );
  }
}

const mapDispatchToProps = (dispatch: (any) => void): Props => {
  return {
    fetchExternalComponents: (): void => {
      dispatch(fetchExternalComponents());
    },
  };
};

export default connect(null, mapDispatchToProps)(App);
