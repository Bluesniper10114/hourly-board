import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { MonitorsListComponent } from './monitors-list.component';

describe('MonitorsListComponent', () => {
  let component: MonitorsListComponent;
  let fixture: ComponentFixture<MonitorsListComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ MonitorsListComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(MonitorsListComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
