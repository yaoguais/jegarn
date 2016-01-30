package com.jegarn.jegarn.manager;

import java.util.Collection;
import java.util.Collections;
import java.util.Set;
import java.util.concurrent.CopyOnWriteArraySet;

public class BaseManager<T> {
    private Set<T> managerListeners = new CopyOnWriteArraySet<>();

    public void addListener(T listener) {
        managerListeners.add(listener);
    }

    public void removeListener(T listener) {
        managerListeners.remove(listener);
    }

    public Collection<T> getListeners() {
        return Collections.unmodifiableCollection(managerListeners);
    }
}