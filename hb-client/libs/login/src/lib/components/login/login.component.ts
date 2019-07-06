import { Component, OnInit, ViewEncapsulation, OnDestroy } from '@angular/core';
import { FormGroup, FormBuilder, Validators } from '@angular/forms';
import { MatSnackBar } from '@angular/material';
import { Subscription } from 'rxjs';

import {
  ISpinnerConfig,
  SPINNER_PLACEMENT,
  SPINNER_ANIMATIONS
} from '@hardpool/ngx-spinner';

import {
  fuseAnimations,
  FuseConfigService,
  NotifyService,
  AuthService
} from '@hourly-board-workspace/shared/fuse';

import { LoginService } from '../../services/login.service';
import { Router } from '@angular/router';

@Component({
  selector: 'hb-login',
  templateUrl: './login.component.html',
  styleUrls: ['./login.component.scss'],
  encapsulation: ViewEncapsulation.None,
  animations: fuseAnimations
})
export class LoginComponent implements OnInit, OnDestroy {
  loginForm: FormGroup;
  loginFormErrors: any;
  loginSbumiting = false;
  config: ISpinnerConfig;

  onErrorReturn: Subscription;
  onLogin: Subscription;
  constructor(
    private _fuseConfigService: FuseConfigService,
    private formBuilder: FormBuilder,
    public snackBar: MatSnackBar,
    private loginService: LoginService,
    private authService: AuthService,
    private notify: NotifyService,
    private router: Router
  ) {
    this.config = {
      size: '3.5rem',
      color: '#1976d2',
      placement: SPINNER_PLACEMENT.block_window,
      animation: SPINNER_ANIMATIONS.rotating_dots
    };
    this.setFuseConfig();
  }

  setFuseConfig(): void {
    // Configure the layout
    this._fuseConfigService.setConfig({
      layout: {
        navbar: {
          hidden: true
        },
        toolbar: {
          hidden: true
        },
        footer: {
          hidden: false
        },
        sidepanel: {
          hidden: true
        }
      }
    });
  }

  ngOnInit() {
    this.loginForm = this.formBuilder.group({
      username: ['', [Validators.required]],
      password: ['', Validators.required]
    });

    this.onErrorReturn = this.loginService.onError.subscribe((err: any) => {
      // debugger;
      if (err) {
        this.notify.showError(
          err.status.toString(),
          err.message ? err.message : err.error
        );

        this.loginSbumiting = false;
        // Configure the layout
        this.setFuseConfig();
      }
    });
  }

  ngOnDestroy(): void {
    this.onErrorReturn.unsubscribe();
    this.onLogin.unsubscribe();
  }

  loginFormSubmit() {
    this.loginSbumiting = !this.loginSbumiting;
    if (this.loginForm.valid) {
      this.onLogin = this.loginService
        .login(this.loginForm.getRawValue())
        .subscribe(response => {
          debugger;
          this.loginSbumiting = !this.loginSbumiting;
          response.token = 'ABCDEFGH123456789';
          this.authService.setToken(
            response,
            this.loginForm.getRawValue().username
          );
          this.router.navigate(['/management/planning-overview']);
        });
    }
  }

  openSnackBar(message: string, action: string) {
    this.snackBar.open(message, action, {
      duration: 2000
    });
  }
}
