import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { MigrateUserComponent } from './migrate-user.component';

describe('MigrateUserComponent', () => {
  let component: MigrateUserComponent;
  let fixture: ComponentFixture<MigrateUserComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ MigrateUserComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(MigrateUserComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
