import 'hammerjs';
import { BrowserModule } from '@angular/platform-browser';
import { NgModule } from '@angular/core';
import { BrowserAnimationsModule } from '@angular/platform-browser/animations';

import { AppComponent } from './app.component';
import { NxModule } from '@nrwl/nx';
import { FuseThemeOptionsModule, FuseSidebarModule, FuseProgressBarModule, FuseModule, fuseConfig, SharedFuseModule } from '@hourly-board-workspace/shared/fuse';
import { SharedLayoutModule } from '@hourly-board-workspace/shared/layout';
import { AppRoutingModule } from './app-routing.module';
import { HttpClientModule } from '@angular/common/http';
import { TranslateModule } from '@ngx-translate/core';

@NgModule({
  declarations: [AppComponent],
  imports: [
    BrowserModule,
    BrowserAnimationsModule,
    HttpClientModule,
    TranslateModule.forRoot(),


    NxModule.forRoot(),
    SharedFuseModule,
    SharedLayoutModule,
    FuseModule.forRoot(fuseConfig),
    FuseProgressBarModule,
    FuseSidebarModule,
    FuseThemeOptionsModule,

    AppRoutingModule
  ],
  providers: [],
  bootstrap: [AppComponent]
})
export class AppModule {}
