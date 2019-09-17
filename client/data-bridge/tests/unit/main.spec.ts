import {
	BRIDGE_INIT,
} from '@/store/actionTypes';
import { launch } from '@/main';
import Vue from 'vue';
import App from '@/presentation/App.vue';
import { EventEmitter } from 'events';
import Events from '@/events';

const mockApp = {
	$mount: jest.fn(),
};
mockApp.$mount.mockImplementation( () => mockApp );
jest.mock( '@/presentation/App.vue', () => {
	return jest.fn().mockImplementation( () => mockApp );
} );

const mockEmitter = {};
jest.mock( 'events', () => ( {
	__esModule: true,
	EventEmitter: jest.fn(),
} ) );
( EventEmitter as unknown as jest.Mock ).mockImplementation( () => mockEmitter );

jest.mock( 'vue', () => {
	return {
		directive: jest.fn(),
		config: {
			productionTip: true,
		},
	};
} );

const store = {
	dispatch: jest.fn(),
};
const mockCreateStore = jest.fn( ( _x: any ) => store );
jest.mock( '@/store', () => ( {
	__esModule: true,
	createStore: ( services: any ) => mockCreateStore( services ),
} ) );

const mockRepeater = jest.fn();
jest.mock( '@/events/repeater', () => ( {
	__esModule: true,
	default: ( app: any, emitter: any, events: any ) => mockRepeater( app, emitter, events ),
} ) );

const inlanguageDirective = {};
const mockInlanguage = jest.fn( ( _x: any ) => inlanguageDirective );
jest.mock( '@/presentation/directives/inlanguage', () => ( {
	__esModule: true,
	default: ( languageRepo: any ) => mockInlanguage( languageRepo ),

} ) );

describe( 'launch', () => {

	it( 'modifies Vue', () => {
		const languageRepo = {};
		const services = {
			getLanguageInfoRepository() {
				return languageRepo;
			},
		};
		const information = {};
		const configuration = {
			containerSelector: '',
		};

		launch( configuration, information as any, services as any );
		expect( mockInlanguage ).toHaveBeenCalledWith( languageRepo );
		expect( Vue.directive ).toHaveBeenCalledWith( 'inlanguage', inlanguageDirective );
		expect( Vue.config.productionTip ).toBe( false );
	} );

	it( 'builds app', () => {
		const languageRepo = {};
		const services = {
			getLanguageInfoRepository() {
				return languageRepo;
			},
		};

		const information = {};
		const configuration = {
			containerSelector: '',
		};

		const emitter = launch( configuration, information as any, services as any );

		expect( emitter ).toBe( mockEmitter );
		expect( mockCreateStore ).toHaveBeenCalledWith( services );
		expect( store.dispatch ).toHaveBeenCalledWith( BRIDGE_INIT, information );
		expect( App ).toHaveBeenCalledWith( { store } );
		expect( mockApp.$mount ).toHaveBeenCalledWith( configuration.containerSelector );
		expect( mockRepeater ).toHaveBeenCalledWith(
			mockApp,
			mockEmitter,
			Object.values( Events ),
		);
	} );
} );