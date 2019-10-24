import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { SocietyNameReservationComponent } from './society-name-reservation.component';

describe('SocietyNameReservationComponent', () => {
  let component: SocietyNameReservationComponent;
  let fixture: ComponentFixture<SocietyNameReservationComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ SocietyNameReservationComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(SocietyNameReservationComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
