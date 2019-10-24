import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { NameWithAgreeReservationComponent } from './name-with-agree-reservation.component';

describe('NameWithAgreeReservationComponent', () => {
  let component: NameWithAgreeReservationComponent;
  let fixture: ComponentFixture<NameWithAgreeReservationComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ NameWithAgreeReservationComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(NameWithAgreeReservationComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
