import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterModule } from '@angular/router';
import { NgxSpinnerModule } from '@hardpool/ngx-spinner';
import { LoginComponent } from './components/login/login.component';
import {
  SharedFuseModule,
  HttpHeaderInterceptorService,
  ErrorInterceptorService
} from '@hourly-board-workspace/shared/fuse';
import { LoginService } from './services/login.service';
import { HttpClientModule, HTTP_INTERCEPTORS } from '@angular/common/http';

@NgModule({
  imports: [
    CommonModule,
    SharedFuseModule,
    NgxSpinnerModule,
    HttpClientModule,
    RouterModule.forChild([
      {
        path: '',
        component: LoginComponent
      }
    ])
  ],
  declarations: [LoginComponent],
  providers: [
    LoginService,
    {
      provide: HTTP_INTERCEPTORS,
      useClass: HttpHeaderInterceptorService,
      multi: true
    },
    {
      provide: HTTP_INTERCEPTORS,
      useClass: ErrorInterceptorService,
      multi: true
    }
  ]
})
export class LoginModule {}
