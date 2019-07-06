import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { EmptyListHolderComponent } from './empty-list-holder.component';

describe('EmptyListHolderComponent', () => {
  let component: EmptyListHolderComponent;
  let fixture: ComponentFixture<EmptyListHolderComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ EmptyListHolderComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(EmptyListHolderComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
