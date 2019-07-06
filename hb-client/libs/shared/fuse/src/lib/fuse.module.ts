import {
  ModuleWithProviders,
  NgModule,
  Optional,
  SkipSelf
} from '@angular/core';

import { FUSE_CONFIG } from './services/config.service';
import {
  SlugService,
  HttpHeaderInterceptorService,
  ErrorInterceptorService,
  AuthService
} from './services';
import { AuthGuard } from './guards';
import { HTTP_INTERCEPTORS } from '@angular/common/http';

@NgModule()
export class FuseModule {
  constructor(
    @Optional()
    @SkipSelf()
    parentModule: FuseModule
  ) {
    if (parentModule) {
      throw new Error(
        'FuseModule is already loaded. Import it in the AppModule only!'
      );
    }
  }

  static forRoot(config): ModuleWithProviders {
    return {
      ngModule: FuseModule,
      providers: [
        {
          provide: FUSE_CONFIG,
          useValue: config
        },
        {
          provide: HTTP_INTERCEPTORS,
          useClass: HttpHeaderInterceptorService,
          multi: true
        },
        {
          provide: HTTP_INTERCEPTORS,
          useClass: ErrorInterceptorService,
          multi: true
        },
        AuthService,
        AuthGuard,
        SlugService
      ]
    };
  }
}
