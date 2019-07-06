import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { LineDateSearchHeaderComponent } from './line-date-search-header.component';

describe('LineDateSearchHeaderComponent', () => {
  let component: LineDateSearchHeaderComponent;
  let fixture: ComponentFixture<LineDateSearchHeaderComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ LineDateSearchHeaderComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(LineDateSearchHeaderComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
