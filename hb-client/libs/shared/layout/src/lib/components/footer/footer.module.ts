import { NgModule } from '@angular/core';
import { RouterModule } from '@angular/router';
import {
  MatButtonModule,
  MatIconModule,
  MatToolbarModule
} from '@angular/material';

import * as fromSharedFuse from '@hourly-board-workspace/shared/fuse';

import { FooterComponent } from './footer.component';
import { FooterService } from './footer.service';

@NgModule({
  declarations: [FooterComponent],
  imports: [RouterModule, fromSharedFuse.SharedFuseModule],
  exports: [FooterComponent],
  providers: [FooterService]
})
export class FooterModule {}
