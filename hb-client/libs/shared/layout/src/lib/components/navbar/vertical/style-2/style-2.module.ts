import { NgModule } from '@angular/core';
import { MatButtonModule, MatIconModule } from '@angular/material';
import { NavbarVerticalStyle2Component } from './style-2.component';
import { FuseNavigationModule, SharedFuseModule } from '@hourly-board-workspace/shared/fuse';

@NgModule({
  declarations: [NavbarVerticalStyle2Component],
  imports: [
    MatButtonModule,
    MatIconModule,

    SharedFuseModule,
    FuseNavigationModule
  ],
  exports: [NavbarVerticalStyle2Component]
})
export class NavbarVerticalStyle2Module {}
