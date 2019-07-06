import { NgModule } from '@angular/core';
import { FormsModule, ReactiveFormsModule } from '@angular/forms';
import { CommonModule } from '@angular/common';

import { FlexLayoutModule } from '@angular/flex-layout';
import { FuseDirectivesModule } from './directives/directives';
import { FusePipesModule } from './pipes/pipes.module';
import { MaterialModule } from './material.module';
import { RatingModule } from 'ngx-rating';
import { NgxSpinnerModule } from '@hardpool/ngx-spinner';
import { BarRatingModule } from "ngx-bar-rating";
import { EmptyListHolderComponent } from './components/empty-list-holder/empty-list-holder.component';

@NgModule({
  imports: [
    CommonModule,
    FormsModule,
    ReactiveFormsModule,
    FlexLayoutModule,
    FuseDirectivesModule,
    FusePipesModule,
    MaterialModule,
    RatingModule,
    NgxSpinnerModule,
    BarRatingModule

  ],
  exports: [
    CommonModule,
    FormsModule,
    ReactiveFormsModule,

    FlexLayoutModule,

    FuseDirectivesModule,
    FusePipesModule,
    MaterialModule,
    RatingModule,
    NgxSpinnerModule,
    BarRatingModule,
    EmptyListHolderComponent
  ],
  declarations: [EmptyListHolderComponent]
})
export class SharedFuseModule {}
